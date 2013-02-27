package applab.search.server;

import applab.Location;
import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.DatabaseTable;
import applab.server.SalesforceProxy;
import applab.server.SelectCommand;
import applab.server.ServletRequestContext;
import applab.server.WebAppId;
import com.sforce.soap.enterprise.LoginResult;
import com.sforce.soap.enterprise.SessionHeader;
import com.sforce.soap.enterprise.SforceServiceLocator;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.schemas._class.CreateSearchLogEntry.CreateSearchLogEntryBindingStub;
import com.sforce.soap.schemas._class.CreateSearchLogEntry.CreateSearchLogEntryServiceLocator;
import com.sforce.soap.schemas._class.CreateSearchLogEntry.SearchLogEntry;
import java.io.IOException;
import java.io.PrintStream;
import java.net.URLDecoder;
import java.rmi.RemoteException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Calendar;
import java.util.HashMap;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.rpc.ServiceException;

public class Search extends ApplabServlet
{
  private static final long serialVersionUID = 1L;
  public static final String NAMESPACE = "http://schemas.applab.org/2010/07/search";
  private static final String CACHED_CONTENT_LOG_MESSAGE = "Cached Content. Inbox access log.";
  private static final String CONTENT_NOT_FOUND_LOG_MESSAGE = "Content Not Found";
  private static final String CONTENT_NOT_FOUND_RESPONSE_MESSAGE = "No content could be associated with your keyword. \n Try downloading an updated list of keywords and repeating your search. \nIf your problem persists, please report this error.";
  private static final String KEYWORD_PARAM = "keyword";
  private static final String INTERVIEWEE_ID_PARAM = "intervieweeId";
  private static final String LOG_ONLY_PARAM = "log";
  private static final String LOCATION_PARAM = "location";
  private static final String CATEGORY_PARAM = "category";
  private static final String HANDSET_SUBMISSION_TIME_PARAM = "submissionTime";

  protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws Exception
  {
    doApplabPost(request, response, context);
  }

  protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws Exception
  {
    String keyword = request.getParameter("keyword");
    Boolean isCachedQuery = Boolean.valueOf(request.getParameter("log") != null);
    String intervieweeId = request.getParameter("intervieweeId");
    String location = request.getParameter("location");
    String category = request.getParameter("category");
    String submissionTime = request.getParameter("submissionTime");

    if ((keyword == null) || (intervieweeId == null) || (location == null) || (submissionTime == null)) {
      context.writeText("Some required parameters are missing.");
    }
    else
    {
      keyword = URLDecoder.decode(keyword, "UTF-8");
      intervieweeId = URLDecoder.decode(intervieweeId, "UTF-8");
      location = URLDecoder.decode(location, "UTF-8");
      submissionTime = URLDecoder.decode(submissionTime, "UTF-8");
      if (category != null) {
        category = URLDecoder.decode(category, "UTF-8");
      }

      HashMap content = null;

      if (!isCachedQuery.booleanValue()) {
        content = getContent(keyword);
        writeResponse(content, context);
      }

      logSearchRequest(context.getHandsetId(), intervieweeId, keyword, 
        content, location, isCachedQuery, submissionTime, context.getSubmissionLocation(), category);
    }

    context.close();
  }

  public static void logSearchRequest(String handsetId, String intervieweeId, String keyword, HashMap<String, String> contentHash, String location, Boolean isCachedQuery, String submissionTime, String submissionLocation, String category)
    throws SQLException, RemoteException, ServiceException
  {
    String content = null;

    if (isCachedQuery.booleanValue()) {
      content = "Cached Content. Inbox access log.";
    }
    else if (contentHash == null) {
      content = "Content Not Found";
    }
    else {
      content = (String)contentHash.get("content");
      if (content.length() > 90) {
        content = content.substring(0, 90);
      }

    }

    CreateSearchLogEntryServiceLocator serviceLocator = new CreateSearchLogEntryServiceLocator();
    CreateSearchLogEntryBindingStub serviceStub = (CreateSearchLogEntryBindingStub)serviceLocator.getCreateSearchLogEntry();

    SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
    soapServiceLocator.setSoapEndpointAddress((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceAddress", ""));
    SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
    LoginResult loginResult = binding.login((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceUsername", ""), 
      (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "") + 
      (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceToken", ""));
    SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

    serviceStub.setHeader("http://soap.sforce.com/schemas/class/CreateSearchLogEntry", "SessionHeader", sessionHeader);

    SearchLogEntry searchLogEntry = new SearchLogEntry();
    searchLogEntry.setHandsetId(handsetId);

    searchLogEntry.setCategory(category);
    searchLogEntry.setContent(content);
    searchLogEntry.setFarmerId(intervieweeId);
    searchLogEntry.setSubmissionTime(submissionTime);

    Calendar calendar = Calendar.getInstance();
    String serverEntryTime = SalesforceProxy.formatDateTime(calendar.getTime());
    searchLogEntry.setServerEntryTime(serverEntryTime);

    Location locationData = Location.parseLocation(location);
    Location submissionData = Location.parseLocation(submissionLocation);

    if (locationData.latitude.floatValue() == 0.0F) {
      locationData = submissionData;
    }
    searchLogEntry.setLatitude(locationData.latitude.toString());
    searchLogEntry.setLongitude(locationData.longitude.toString());
    searchLogEntry.setAltitude(locationData.altitude.toString());
    searchLogEntry.setAccuracy(locationData.accuracy.toString());

    searchLogEntry.setSubmissionAccuracy(submissionData.accuracy.toString());
    searchLogEntry.setSubmissionAltitude(submissionData.altitude.toString());
    searchLogEntry.setSubmissionLatitude(submissionData.latitude.toString());
    searchLogEntry.setSubmissionLongitude(submissionData.longitude.toString());
    searchLogEntry.setSubmissionGPSTime(String.valueOf(submissionData.timestamp));
    System.out.println("Category: " + category);

    searchLogEntry.setQuery(keyword);

    SearchLogEntry resultSearchLogEntry = serviceStub.createNewSearchLogEntry(searchLogEntry);

    resultSearchLogEntry.getInserted().booleanValue();
  }

  private static void writeResponse(HashMap<String, String> content, ServletRequestContext context)
    throws IOException, ClassNotFoundException, SQLException
  {
    if (content != null) {
      context.writeText((String)content.get("content"));
    }
    else
      context.writeText("No content could be associated with your keyword. \n Try downloading an updated list of keywords and repeating your search. \nIf your problem persists, please report this error.");
  }

  public static HashMap<String, String> getContent(String keyword) throws Exception
  {
    HashMap results = new HashMap();
    String keywordTableName = DatabaseTable.Keyword.getTableName();
    String categoryTableName = DatabaseTable.Category.getTableName();

    SelectCommand select = new SelectCommand(DatabaseTable.Keyword);
    try {
      select.addField(keywordTableName + ".content");
      select.addField(keywordTableName + ".attribution");
      select.addField(keywordTableName + ".updated");
      select.addField(categoryTableName + ".name");

      select.whereEquals("REPLACE(" + keywordTableName + ".keyword, '_', ' ')", "'" + keyword + "'");
      select.whereEquals(keywordTableName + ".isDeleted", "0");
      select.whereEquals(categoryTableName + ".isDeleted", "0");
      select.innerJoin(DatabaseTable.Category, keywordTableName + ".categoryId = " + categoryTableName + ".id");
      select.limit(Integer.valueOf(1));
      ResultSet resultSet = select.execute();
      if (resultSet.next()) {
        String content = resultSet.getString("content");
        if ((content != null) && (content.trim().length() > 0)) {
          content = content.trim().replace("\r\n", "\n");
        }
        String attribution = resultSet.getString("attribution");
        if ((attribution != null) && (attribution.trim().length() > 0)) {
          content = content + "\n\nAttribution: " + attribution.trim().replace("\r\n", "\n");
        }
        String updated = resultSet.getString("updated");
        if ((updated != null) && (updated.trim().length() > 0)) {
          content = content + "\n\nLast Updated: " + updated.trim().replace("\r\n", "\n");
        }
        results.put("content", content);
        results.put("category", resultSet.getString("name"));
        results.put("keyword", keyword);
      }
      else {
        results = null;
      }
    }
    catch (Exception e)
    {
      throw e;
    }
    finally {
      select.dispose();
    }
    return results;
  }
}