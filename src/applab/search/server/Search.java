package applab.search.server;

import java.io.IOException;
import java.net.URLDecoder;
import java.rmi.RemoteException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Calendar;
import java.util.HashMap;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.rpc.ServiceException;

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

public class Search extends ApplabServlet {

    private static final long serialVersionUID = 1L;
    public final static String NAMESPACE = "http://schemas.applab.org/2010/07/search";
    private final static String CACHED_CONTENT_LOG_MESSAGE = "Cached Content. Inbox access log.";
    private final static String CONTENT_NOT_FOUND_LOG_MESSAGE = "Content Not Found";
    private final static String CONTENT_NOT_FOUND_RESPONSE_MESSAGE = "No content could be associated with your keyword. \n Try downloading an updated list of keywords and repeating your search. \nIf your problem persists, please report this error.";
    private final static String KEYWORD_PARAM = "keyword";
    private final static String INTERVIEWEE_ID_PARAM = "intervieweeId";
    private final static String LOG_ONLY_PARAM = "log";
    private final static String LOCATION_PARAM = "location";
    private final static String CATEGORY_PARAM = "category";
    private final static String HANDSET_SUBMISSION_TIME_PARAM = "submissionTime";

    /**
     * For now, we're using an http get for this /.../search?keyword=keyword&intervieweeId=XYZ&.... We will shift to xml
     * post as per spec when the client side changes are ready to be made
     * 
     * @throws Exception
     */
    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws Exception {
        doApplabPost(request, response, context);
    }

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws Exception {
        String keyword = request.getParameter(KEYWORD_PARAM);
        Boolean isCachedQuery = (request.getParameter(LOG_ONLY_PARAM) != null);
        String intervieweeId = request.getParameter(INTERVIEWEE_ID_PARAM);
        String location = request.getParameter(LOCATION_PARAM);
        String category = request.getParameter(CATEGORY_PARAM);
        String submissionTime = request.getParameter(HANDSET_SUBMISSION_TIME_PARAM);

        if (keyword == null || intervieweeId == null || location == null || submissionTime == null) {
            context.writeText("Some required parameters are missing.");
        }
        else {
            // some URL decoding
            keyword = URLDecoder.decode(keyword, "UTF-8");
            intervieweeId = URLDecoder.decode(intervieweeId, "UTF-8");
            location = URLDecoder.decode(location, "UTF-8");
            submissionTime = URLDecoder.decode(submissionTime, "UTF-8");
            if (category != null) {
                category = URLDecoder.decode(category, "UTF-8");
            }

            HashMap<String, String> content = null;

            if (!isCachedQuery) {
                content = getContent(keyword);
                Search.writeResponse(content, context);
            }

            logSearchRequest(context.getHandsetId(), intervieweeId, keyword,
                    content, location, isCachedQuery, submissionTime, context.getSubmissionLocation(), category);
        }

        context.close();
    }

    public static void logSearchRequest(String handsetId, String intervieweeId, String keyword,
                                        HashMap<String, String> contentHash, String location, Boolean isCachedQuery,
                                        String submissionTime, String submissionLocation, String category)
            throws SQLException, RemoteException, ServiceException {
        String content = null;

        if (isCachedQuery) {
            content = CACHED_CONTENT_LOG_MESSAGE;
        }
        else if (contentHash == null) {
            content = CONTENT_NOT_FOUND_LOG_MESSAGE;
        }
        else {
            content = contentHash.get("content");
            if (content.length() > 90) {
                content = content.substring(0, 90);
            }

        }

        // TODO: Do we want to call out the category if this is a cachedquery? Or leave it as part of the keyword?
        // String category = null;
        // if (contentHash == null) {
        // category = CONTENT_NOT_FOUND_LOG_MESSAGE;
        // }
        // else {
        // category = contentHash.get(DatabaseTable.Category.getTableName() + ".name");
        // }

        // Save hit to SF

        CreateSearchLogEntryServiceLocator serviceLocator = new CreateSearchLogEntryServiceLocator();
        CreateSearchLogEntryBindingStub serviceStub = (CreateSearchLogEntryBindingStub)serviceLocator.getCreateSearchLogEntry();

        // Use soap api to login and get session info
        SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
        soapServiceLocator.setSoapEndpointAddress((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceAddress", ""));
        SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
        LoginResult loginResult = binding.login((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceUsername", ""),
                (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "")
                        + (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceToken", ""));
        SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

        // Share the session info with our webservice
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

        // Get the location data
        Location locationData = Location.parseLocation(location);
        Location submissionData = Location.parseLocation(submissionLocation);

        // Check that we got the location from the search. If not use the one that is in the header
        if (locationData.latitude == 0) {
            locationData = submissionData;
        }
        searchLogEntry.setLatitude(locationData.latitude.toString());
        searchLogEntry.setLongitude(locationData.longitude.toString());
        searchLogEntry.setAltitude(locationData.altitude.toString());
        searchLogEntry.setAccuracy(locationData.accuracy.toString());

        // Get the location that the search was submitted from.
        searchLogEntry.setSubmissionAccuracy(submissionData.accuracy.toString());
        searchLogEntry.setSubmissionAltitude(submissionData.altitude.toString());
        searchLogEntry.setSubmissionLatitude(submissionData.latitude.toString());
        searchLogEntry.setSubmissionLongitude(submissionData.longitude.toString());
        searchLogEntry.setSubmissionGPSTime(String.valueOf(submissionData.timestamp));
System.out.println("Category: " + category);

        searchLogEntry.setQuery(keyword);

        SearchLogEntry resultSearchLogEntry = serviceStub.createNewSearchLogEntry(searchLogEntry);

        if (!resultSearchLogEntry.getInserted()) {
            // Do nothing for now
        }
    }

    private static void writeResponse(HashMap<String, String> content,
                                      ServletRequestContext context) throws IOException, ClassNotFoundException, SQLException {
        if (content != null) {
            context.writeText(content.get("content"));
        }
        else {
            context.writeText(CONTENT_NOT_FOUND_RESPONSE_MESSAGE);
        }
    }

    public static HashMap<String, String> getContent(String keyword) throws Exception {
        HashMap<String, String> results = new HashMap<String, String>();
        String keywordTableName = DatabaseTable.Keyword.getTableName();
        String categoryTableName = DatabaseTable.Category.getTableName();

        SelectCommand select = new SelectCommand(DatabaseTable.Keyword);
        try {
            select.addField(keywordTableName + ".content");
            select.addField(keywordTableName + ".attribution");
            select.addField(keywordTableName + ".updated");
            select.addField(categoryTableName + ".name"); // Get the category as well

            // We need to do this because keywords are stored with _
            // TODO: there's a small chance that this replacement will cause us to return wrong content (a_b c <==> a
            // b_c)
            select.whereEquals("REPLACE(" + keywordTableName + ".keyword, '_', ' ')", "'" + keyword + "'");
            select.whereEquals(keywordTableName + ".isDeleted", "0");
            select.whereEquals(categoryTableName + ".isDeleted", "0");
            select.innerJoin(DatabaseTable.Category, keywordTableName + ".categoryId = " + categoryTableName + ".id");
            select.limit(1);
            ResultSet resultSet = select.execute();
            if (resultSet.next()) {
                String content = resultSet.getString("content");
                if (content != null && content.trim().length() > 0) {
                    content = content.trim().replace("\r\n", "\n");
                }
                String attribution = resultSet.getString("attribution");
                if (attribution != null && attribution.trim().length() > 0) {
                    content += "\n\nAttribution: " + attribution.trim().replace("\r\n", "\n");
                }
                String updated = resultSet.getString("updated");
                if (updated != null && updated.trim().length() > 0) {
                    content += "\n\nLast Updated: " + updated.trim().replace("\r\n", "\n");
                }
                results.put("content", content);
                results.put("category", resultSet.getString("name"));
                results.put("keyword", keyword);
            }
            else {
                results = null;
            }
        }
        catch (Exception e) {
            // TODO: handle exception
            throw e;
        }
        finally {
            select.dispose();
        }
        return results;
    }
}
