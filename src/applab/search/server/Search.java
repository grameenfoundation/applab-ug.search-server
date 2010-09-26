package applab.search.server;

import java.io.IOException;
import java.rmi.RemoteException;
import java.sql.Array;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.HashMap;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;
import javax.xml.soap.SOAPHeaderElement;

import org.xml.sax.SAXException;

import com.sforce.soap.enterprise.LoginResult;
import com.sforce.soap.enterprise.SessionHeader;
import com.sforce.soap.enterprise.SforceService;
import com.sforce.soap.enterprise.SforceServiceLocator;
import com.sforce.soap.enterprise.Soap;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.schemas._class.CreateSearchLogEntry.CreateSearchLogEntryBindingStub;
import com.sforce.soap.schemas._class.CreateSearchLogEntry.CreateSearchLogEntryServiceLocator;
import com.sforce.soap.schemas._class.CreateSearchLogEntry.SearchLogEntry;

import applab.Location;
import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.DatabaseTable;
import applab.server.SalesforceProxy;
import applab.server.SelectCommand;
import applab.server.ServletRequestContext;

public class Search extends ApplabServlet {

    private static final long serialVersionUID = 1L;
    public final static String NAMESPACE = "http://schemas.applab.org/2010/07/search";
    private final static String RESPONSE_ELEMENT_NAME = "SearchResponse";
    private final static String CACHED_CONTENT_LOG_MESSAGE = "Cached Content. Inbox access log.";
    private final static String CONTENT_NOT_FOUND_LOG_MESSAGE = "Content Not Found";
    private final static String CONTENT_NOT_FOUND_RESPONSE_MESSAGE = "No content could be associated with your keyword. \n Try downloading an updated list of keywords and repeating your search. \nIf your problem persists, please report this error.";
    private final static String KEYWORD_PARAM = "keyword";
    private final static String INTERVIEWEE_ID_PARAM = "intervieweeId";
    private final static String LOG_ONLY_PARAM = "log";
    private final static String LOCATION_PARAM = "location";
    private final static String HANDSET_SUBMISSION_TIME_PARAM = "submissionTime";

    /**
     * For now, we're using an http get for this /.../search?keyword=keyword&intervieweeId=XYZ&....
     * We will shift to xml post as per spec when the client side changes are ready to be made
     */
    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws IOException, SAXException, ParserConfigurationException, ClassNotFoundException, SQLException, ServiceException {
        doApplabPost(request, response, context);
    }
    
    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws IOException, SAXException, ParserConfigurationException, ClassNotFoundException, SQLException, ServiceException {
        String keyword = request.getParameter(KEYWORD_PARAM);
        Boolean isCachedQuery = (request.getParameter(LOG_ONLY_PARAM) != null);
        String intervieweeId = request.getParameter(INTERVIEWEE_ID_PARAM);
        String location = request.getParameter(LOCATION_PARAM);
        String handsetSubmitTime = request.getParameter(HANDSET_SUBMISSION_TIME_PARAM);
        
        if(keyword == null || intervieweeId == null || location == null || handsetSubmitTime == null) {
            context.writeText("Some required parameters are missing.");
        }
        else {
            HashMap<String, String> content = null;
    
            if (!isCachedQuery) {
                content = getContent(keyword);
                Search.writeResponse(content, context);
            }
    
            logSearchRequest(context.getHandsetId(), intervieweeId, keyword,
                    content, location, isCachedQuery, handsetSubmitTime);
        }
        
        context.close();
    }

    public static void logSearchRequest(String handsetId, String intervieweeId,String keyword,
                                   HashMap<String, String> contentHash, String location, Boolean isCachedQuery, String handsetSubmitTime)
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

        }

        String category = null;
        if (contentHash == null) {
            category = CONTENT_NOT_FOUND_LOG_MESSAGE;
        }
        else {
            category = contentHash.get(DatabaseTable.Category.getTableName() + ".name");
        }

        // Save hit to SF

        CreateSearchLogEntryServiceLocator serviceLocator = new CreateSearchLogEntryServiceLocator();
        CreateSearchLogEntryBindingStub serviceStub = (CreateSearchLogEntryBindingStub)serviceLocator.getCreateSearchLogEntry();

        // Use soap api to login and get session info

        SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
        soapServiceLocator.setSoapEndpointAddress(ApplabConfiguration.getSalesforceAddress());
        SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
        LoginResult loginResult = binding.login(ApplabConfiguration.getSalesforceUsername(), ApplabConfiguration.getSalesforcePassword()
                + ApplabConfiguration.getSalesforceToken());
        SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

        // Share the session info with our webservice
        serviceStub.setHeader("http://soap.sforce.com/schemas/class/CreateSearchLogEntry", "SessionHeader", sessionHeader);

        SearchLogEntry searchLogEntry = new SearchLogEntry();
        searchLogEntry.setHandsetId(handsetId);

        searchLogEntry.setCategory(category);
        searchLogEntry.setContent(content);
        searchLogEntry.setFarmerId(intervieweeId);
        searchLogEntry.setHandsetSubmitTime(handsetSubmitTime);

        Calendar calendar = Calendar.getInstance();
        String serverEntryTime = SalesforceProxy.formatDateTime(calendar.getTime());
        searchLogEntry.setServerEntryTime(serverEntryTime);

        // Get the location data
        Location locationData = Location.parseLocation(location);
        searchLogEntry.setLatitude(locationData.latitude.toString());
        searchLogEntry.setLongitude(locationData.longitude.toString());
        searchLogEntry.setAltitude(locationData.altitude.toString());
        searchLogEntry.setAccuracy(locationData.accuracy.toString());

        searchLogEntry.setQuery(keyword);

        SearchLogEntry resultSearchLogEntry = serviceStub.createNewSearchLogEntry(searchLogEntry);

        if (!resultSearchLogEntry.getInserted()) {
            // Do nothing for now
        }
    }

    private static void writeResponse(HashMap<String, String> content,
                                      ServletRequestContext context) throws IOException, ClassNotFoundException, SQLException {
        context.writeXmlHeader();
        context.writeStartElement(RESPONSE_ELEMENT_NAME, NAMESPACE);
        if (content != null) {
            context.writeText(content.get("content"));
        }
        else {
            context.writeText(CONTENT_NOT_FOUND_RESPONSE_MESSAGE);
        }
        context.writeEndElement();
    }

    public static HashMap<String, String> getContent(String keyword) throws ClassNotFoundException, SQLException {
        HashMap<String, String> results = new HashMap<String, String>();
        String keywordTableName = DatabaseTable.Keyword.getTableName();
        String categoryTableName = DatabaseTable.Category.getTableName();

        SelectCommand select = new SelectCommand(DatabaseTable.Keyword);
        select.addField(keywordTableName + ".content");
        select.addField(categoryTableName + ".name"); // Get the category as well
        select.whereEquals(keywordTableName + ".keyword", "'" + keyword + "'");
        select.whereEquals(keywordTableName + ".isDeleted", "0");
        select.whereEquals(categoryTableName + ".isDeleted", "0");
        select.innerJoin(DatabaseTable.Category, keywordTableName + ".categoryId = " + categoryTableName + ".id");
        select.limit(1);
        ResultSet resultSet = select.execute();
        if (resultSet.next()) {
            results.put("content", resultSet.getString("content"));
            results.put("category", resultSet.getString("name"));
            results.put("keyword", keyword);
        }
        else {
            results = null;
        }
        return results;
    }
}
