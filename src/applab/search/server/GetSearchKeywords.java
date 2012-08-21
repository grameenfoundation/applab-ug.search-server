package applab.search.server;

import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.WebAppId;

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.rmi.RemoteException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Enumeration;
import java.util.Map;
import java.util.Scanner;

import javax.servlet.ServletException;
import javax.servlet.ServletOutputStream;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;

import org.w3c.dom.DOMException;
import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

import com.sforce.soap.enterprise.LoginResult;
import com.sforce.soap.enterprise.SessionHeader;
import com.sforce.soap.enterprise.SforceServiceLocator;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.enterprise.fault.InvalidIdFault;
import com.sforce.soap.enterprise.fault.LoginFault;
import com.sforce.soap.enterprise.fault.UnexpectedErrorFault;
/*import com.sforce.soap.schemas._class.UpdateKeywords.JsonRequest;
import com.sforce.soap.schemas._class.UpdateKeywords.UpdateKeywordsBindingStub;
import com.sforce.soap.schemas._class.UpdateKeywords.UpdateKeywordsServiceLocator;
import com.sforce.soap.schemas._class.UpdateKeywordsFromCache.UpdateKeywordsFromCacheBindingStub;
import com.sforce.soap.schemas._class.UpdateKeywordsFromCache.UpdateKeywordsFromCacheServiceLocator;
*/
/**
 * Servlet implementation class GetRestKeywords
 */
public class GetSearchKeywords extends ApplabServlet {

    private static final long serialVersionUID = 1L;
    private final static String IMEI = "x-Imei";
    private final static String KEYWORDS_LAST_UPDATE_DATE = "localKeywordsVersion";
    private final static String IMAGES_LAST_UPDATE_DATE = "localImagesVersion";
    private final static String MENU_IDS = "menuIds";

    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws Exception {
        log("Reached get method");
        doApplabPost(request, response, context);

    }

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws Exception {
        
        PrintWriter out = response.getWriter();
       /* SearchSalesforceProxy proxy = new SearchSalesforceProxy();
        // set up saleforce authentication to access webservice
        UpdateKeywordsBindingStub serviceStub = setupSalesforceAuthentication();
        UpdateKeywordsFromCacheBindingStub serviceStubCache = setupSalesforceAuthenticationForCache();
        */
        try {
            log("Reached post method");
            // get current date & time to stipulate version
            // this is done before processing to prevent timelags due to latency and processing

            response.setContentType("application/json; charset = UTF-8");

            SimpleDateFormat dateFormat =
                    new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
            Date date = new Date();
            String currentVersion = dateFormat.format(date);            

            String imei = request.getHeader(IMEI);
            log("x-Imei: " + imei);

            Document requestXml = context.getRequestBodyAsXml();
            NodeList keywordsNodeList = requestXml.getElementsByTagName(KEYWORDS_LAST_UPDATE_DATE);
            String keywordsDateString = keywordsNodeList.item(0).getTextContent();

            // set the default images last update date to the same value as keywords updated date
            String imagesDateString = keywordsDateString;
            log("Keywords update String: " + keywordsDateString);

            NodeList imagesNodeList = requestXml.getElementsByTagName(IMAGES_LAST_UPDATE_DATE);
            // Check to ensure that images update date is sent. Pre 4.1.2 Search clients do not send this information
            if (imagesNodeList != null && imagesNodeList.getLength() != 0) {
                imagesDateString = imagesNodeList.item(0).getTextContent();
                log("Keywords update String: " + keywordsDateString);
            }

            NodeList menuList = requestXml.getElementsByTagName(MENU_IDS);
            String[] menuIds = menuList.item(0).getTextContent().split(",");

            if (menuIds != null && menuIds.length != 0) {
                log("menu Ids: " + menuIds[0]);
            }
            else {
                menuIds = new String[0];
                log("No previous menu Ids");
            }
                     /*   
            if ((convertDateStringToDouble(currentVersion) - convertDateStringToDouble(keywordsDateString) > 200 || menuIds.length == 0)
                    && proxy.checkIfImeiIsForPersonInCountryCode(imei, "UG")) {
                log("getting data from SF cache");
                // This is how its supposed to work
                // out.println(serviceStub.getCachedKeywords("CKWSearch"));

                // stop gap solution
                /*
                 * InputStream inStream = context.getServletContext() .getResourceAsStream("/WEB-INF/CKWSearch.txt");
                 * ServletOutputStream outStream = response.getOutputStream();
                 * 
                 * if (inStream != null) { try { byte[] bytes = new byte[457000]; int bytesRead; while ((bytesRead =
                 * inStream.read(bytes)) != -1) { outStream.write(bytes, 0, bytesRead); log("Sent bytes to :" + imei); }
                 * } finally { inStream.close(); outStream.close(); } log("Finished sending data from Cache"); return; }
                 */
                
                // set up saleforce authentication to access webservice
                /*
                out.println(serviceStubCache.getCachedKeywords(""));
                log("Finished sending keywords");
               
            }
            else {
                // build Json request.
            	/*
                JsonRequest req = new JsonRequest();
                req.setImei(imei);
                req.setKeywordsLastUpdatedDate(keywordsDateString);
                req.setImagesLastUpdatedDate(imagesDateString);
                req.setMenuIds(menuIds);

                String[] jsonResults = serviceStub.getKeywords(req);

                // build welformed response for client
                String json = buildJsonResponse(jsonResults, currentVersion);
                out.println(json);
                log("Finished sending keywords");
            }*/
        }
        catch (DOMException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
            log(e.getMessage());
        }
        catch (SAXException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
            log(e.getMessage());
        }
        catch (ParserConfigurationException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
            log(e.getMessage());
        }
        catch (Exception e) {
        	
            // set up saleforce authentication to access webservice
//            out.println(serviceStubCache.getCachedKeywords(""));
            log("Finished sending keywords");
        }
    }

    /**
     * @param jsonResults
     * @return well formatted Json for client parsing
     */
    private String buildJsonResponse(String[] jsonResults, String currentVersion) {

        StringBuilder str = new StringBuilder("{\"Total\":");
        str.append(jsonResults[0]);
        str.append(",\"Version\":\"");
        str.append(currentVersion);
        str.append("\", \"Menus\":");
        str.append(jsonResults[1]);
        str.append(", \"MenuItems\":");
        str.append(jsonResults[2]);
        str.append(", \"DeletedMenuItems\":");
        str.append(jsonResults[3]);
        str.append(", \"Images\":");
        str.append(jsonResults[4]);
        str.append(", \"DeletedImages\":");
        str.append(jsonResults[5]);
        str.append("}");
        return str.toString();
    }

    /**
     * This converts dates using weighted digits to build a double and compare The date is expected in this format
     * "yyyy-MM-dd HH:mm:ss"
     * 
     * @param dateString
     * @return
     */
    private double convertDateStringToDouble(String dateString) {
        String[] splits = dateString.split(" ");
        String[] dateParts = splits[0].split("-");
        if (dateParts.length == 3) {
            return Double.parseDouble(dateParts[0] + dateParts[1] + dateParts[2]);
        }
        return Double.parseDouble(dateParts[0] + "0000");
    }

    /**
     * This authenticates and sets up a service stub for webservice calls
     * 
     * @return UpdateKeywords service stub
     * @throws ServiceException
     * @throws RemoteException
     * @throws InvalidIdFault
     * @throws UnexpectedErrorFault
     * @throws LoginFault
     */
    /*
    private UpdateKeywordsBindingStub setupSalesforceAuthentication() throws ServiceException, RemoteException, InvalidIdFault,
            UnexpectedErrorFault, LoginFault {

        UpdateKeywordsServiceLocator updateKeywordsServiceLocator = new UpdateKeywordsServiceLocator();
        UpdateKeywordsBindingStub serviceStub = (UpdateKeywordsBindingStub)updateKeywordsServiceLocator.getUpdateKeywords();

        // Use soap api to login and get session info
        SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
        soapServiceLocator.setSoapEndpointAddress((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceAddress", ""));
        SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
        LoginResult loginResult = binding.login((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceUsername", ""),
                (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "")
                        + (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceToken", ""));
        SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

        // Share the session info with our webservice
        serviceStub.setHeader("http://soap.sforce.com/schemas/class/UpdateKeywords", "SessionHeader", sessionHeader);
        return serviceStub;
    }

    private UpdateKeywordsFromCacheBindingStub setupSalesforceAuthenticationForCache() throws ServiceException, RemoteException, InvalidIdFault,
            UnexpectedErrorFault, LoginFault {

        UpdateKeywordsFromCacheServiceLocator updateKeywordsServiceLocator = new UpdateKeywordsFromCacheServiceLocator();
        UpdateKeywordsFromCacheBindingStub serviceStub = (UpdateKeywordsFromCacheBindingStub)updateKeywordsServiceLocator.getUpdateKeywordsFromCache();

        // Use soap api to login and get session info
        SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
        soapServiceLocator.setSoapEndpointAddress((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceAddress", ""));
        SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
        LoginResult loginResult = binding.login((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceUsername", ""),
                (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "")
                        + (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceToken", ""));
        SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

        // Share the session info with our webservice
        serviceStub.setHeader("http://soap.sforce.com/schemas/class/UpdateKeywordsFromCache", "SessionHeader", sessionHeader);
        return serviceStub;
    }
    */
}
