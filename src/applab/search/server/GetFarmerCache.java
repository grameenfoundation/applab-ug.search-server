package applab.search.server;

import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.WebAppId;

import java.io.IOException;
import java.io.PrintWriter;
import java.rmi.RemoteException;
import java.text.SimpleDateFormat;
import java.util.Date;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;

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
import com.sforce.soap.schemas._class.FarmerCache.FarmerCacheBindingStub;
import com.sforce.soap.schemas._class.FarmerCache.FarmerCacheServiceLocator;

/**
 * Servlet implementation class GetFarmerIds
 */
public class GetFarmerCache extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static final String IMEI = "x-Imei";
    private static final String LAST_UPDATE_DATE = "localCacheVersion";

    /**
     * Default constructor.
     */
    public GetFarmerCache() {
        // TODO Auto-generated constructor stub
    }

    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {

        log("Reached get method for Get Farmer Cache");
        doApplabPost(request, response, context);
    }

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {

        try {
            log("Reached post method for Get Farmer Cache");
            String imei = request.getHeader(IMEI);
            log("x-Imei: " + imei);
            Document requestXml = context.getRequestBodyAsXml();
            NodeList nodeList = requestXml.getElementsByTagName(LAST_UPDATE_DATE);
            String dateString = nodeList.item(0).getTextContent();
            log("Date String: " + dateString);

            // make Salesforce call
            String jsonResult = getFarmerCacheFromSalesforce(imei, dateString);

            PrintWriter out = response.getWriter();
            out.println(jsonResult);
            log("Finished sending Farmer Cache");
        }
        catch (SAXException e) {

            e.printStackTrace();
        }
        catch (ParserConfigurationException e) {

            e.printStackTrace();
        }
    }

    /**
     * Stub for Saleforce method
     * 
     * @param imei
     * @param lastUpdateDate
     * @return
     * @throws RemoteException
     * @throws ServiceException
     */
    private String getFarmerCacheFromSalesforce(String imei, String lastUpdateDate) throws RemoteException, ServiceException {
        SimpleDateFormat dateFormat =
                new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        Date date = new Date();
        String currentVersion = dateFormat.format(date);

        FarmerCacheBindingStub serviceStub = setupSalesforceAuthentication();
        String result = serviceStub.getFarmerCache(imei, lastUpdateDate);
        
        StringBuilder str = new StringBuilder("{\"Farmers\":");
        str.append(result);
        str.append(",\"Version\":\"");
        str.append(currentVersion);
        str.append("\"}");
        return str.toString();


    }

    private FarmerCacheBindingStub setupSalesforceAuthentication() throws ServiceException, RemoteException, InvalidIdFault,
            UnexpectedErrorFault, LoginFault {

        FarmerCacheServiceLocator farmerCacheServiceLocator = new FarmerCacheServiceLocator();
        FarmerCacheBindingStub serviceStub = (FarmerCacheBindingStub)farmerCacheServiceLocator.getFarmerCache();

        // Use soap api to login and get session info
        SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
        soapServiceLocator.setSoapEndpointAddress((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceAddress", ""));
        SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
        LoginResult loginResult = binding.login((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceUsername", ""),
                (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "")
                        + (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceToken", ""));
        SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

        // Share the session info with our webservice
        serviceStub.setHeader("http://soap.sforce.com/schemas/class/FarmerCache", "SessionHeader", sessionHeader);
        return serviceStub;
    }
}
