package applab.search.server;

import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.WebAppId;

import java.io.IOException;
import java.io.PrintWriter;
import java.rmi.RemoteException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Calendar;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;

import org.w3c.dom.DOMException;
import org.xml.sax.SAXException;

import com.sforce.soap.enterprise.LoginResult;
import com.sforce.soap.enterprise.SessionHeader;
import com.sforce.soap.enterprise.SforceServiceLocator;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.enterprise.fault.InvalidIdFault;
import com.sforce.soap.enterprise.fault.LoginFault;
import com.sforce.soap.enterprise.fault.UnexpectedErrorFault;
import com.sforce.soap.schemas._class.UpdateKeywords.JsonRequest;
import com.sforce.soap.schemas._class.UpdateKeywords.UpdateKeywordsBindingStub;
import com.sforce.soap.schemas._class.UpdateKeywords.UpdateKeywordsServiceLocator;

/**
 * Servlet implementation class GetRestKeywords
 */
public class GetSearchKeywords extends ApplabServlet {

    private static final long serialVersionUID = 1L;
    private final static String IMEI = "Imei";

    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {
        log("Reached get method");
        doApplabPost(request, response, context);

    }

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {

        try {
            log("Reached post method");
            // get current date & time to stipulate version
            // this is done before processing to prevent timelags due to latency and processing
            DateFormat dateFormat = new SimpleDateFormat("yyyy/MM/dd HH:mm:ss");
            Calendar calendar = Calendar.getInstance();
            String currentVersion = dateFormat.format(calendar.getTime());

            // set up saleforce authentication to access webservice
            UpdateKeywordsBindingStub serviceStub = setupSalesforceAuthentication();

            String imei = request.getParameter("x-Imei");
            String dateString = context.getRequestBodyAsXml().getChildNodes().item(2).getNodeValue();
            String[] menuIds = context.getRequestBodyAsXml().getChildNodes().item(3).getNodeValue().split(",");

            // build Json request.
            JsonRequest req = new JsonRequest();
            req.setImei(imei);
            req.setLastUpdatedDate(dateString);
            req.setMenuIds(menuIds);

            log(req.getImei() + " " + req.getLastUpdatedDate());
            String[] jsonResults = serviceStub.getKeywords(req);

            // build welformed response for client
            String json = buildJsonResponse(jsonResults, currentVersion);
            PrintWriter out = response.getWriter();
            out.println(json);
        }
        catch (DOMException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        catch (SAXException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        catch (ParserConfigurationException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
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
     * This authenticates and sets up a service stub for webservice calls
     * 
     * @return UpdateKeywords service stub
     * @throws ServiceException
     * @throws RemoteException
     * @throws InvalidIdFault
     * @throws UnexpectedErrorFault
     * @throws LoginFault
     */
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
}
