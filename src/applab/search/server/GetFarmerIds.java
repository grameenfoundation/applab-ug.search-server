package applab.search.server;

import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.WebAppId;

import java.io.IOException;
import java.io.PrintWriter;
import java.rmi.RemoteException;
import java.util.ArrayList;
import java.util.Random;

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
import com.sforce.soap.schemas._class.PreRegisterFarmers.BulkRegisterFarmers;
import com.sforce.soap.schemas._class.PreRegisterFarmers.PreRegisterFarmersBindingStub;
import com.sforce.soap.schemas._class.PreRegisterFarmers.PreRegisterFarmersServiceLocator;

/**
 * Servlet implementation class GetFarmerIds
 */
public class GetFarmerIds extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static final String IMEI = "x-Imei";
    private static final String CURRENT_FARMER_ID_COUNT = "currentFarmerIdCount";
    public static final int FARMER_ID_SET_SIZE = 100;

    /**
     * Default constructor.
     */
    public GetFarmerIds() {
        // TODO Auto-generated constructor stub
    }

    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {

        log("Reached get method for Get Farmer Ids");
        doApplabPost(request, response, context);
    }

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {

        try {
            log("Reached post method for Get Farmer Ids");
            String imei = request.getHeader(IMEI);
            log("x-Imei: " + imei);
            Document requestXml = context.getRequestBodyAsXml();
            NodeList nodeList = requestXml.getElementsByTagName(CURRENT_FARMER_ID_COUNT);
            String farmerIdString = nodeList.item(0).getTextContent();
            log("Farmer Id Count : " + farmerIdString);
            int farmerIdCount = Integer.parseInt(farmerIdString);
            log("Current Farmer Id Count: " + farmerIdCount);

            // make Salesforce call
            String jsonResult = getFarmerIdsFromSalesforce(imei, farmerIdCount);

            PrintWriter out = response.getWriter();
            out.println(jsonResult);
            log("Finished sending new Farmer Ids");
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
     * @param currentFarmerIdCount
     * @return
     * @throws RemoteException 
     * @throws ServiceException 
     */
    private String getFarmerIdsFromSalesforce(String imei, int currentFarmerIdCount) throws RemoteException, ServiceException {
        /*
         * Random rand = new Random();
        long randomValue1 = Math.round(rand.nextDouble() * 10000);
        long randomValue2 = Math.round(rand.nextDouble() * 10000);
        return String.format("{\"FarmerIds\" : [ {\"Id\" : \"223AB%d\", \"FId\" : \"DF%d\"}, {\"Id\" : \"234CD%d\", \"FId\" : \"CQ%d\"}]}",
                randomValue1, randomValue1, randomValue2, randomValue2); 
        */             
                
        if (currentFarmerIdCount > 10) {
            return "";            
        }        
        int newIdCount = FARMER_ID_SET_SIZE - currentFarmerIdCount;       
        
        //TODO 1. First generate New Codes
        String generatedFarmerIds = generateNewFarmerIds(newIdCount);
        
        //3. Push the IDs to Salesforce as comma seperated
        BulkRegisterFarmers bulkRegisterFarmers = new BulkRegisterFarmers();
        bulkRegisterFarmers.setImei(imei);
        bulkRegisterFarmers.setNewFarmerIds(generatedFarmerIds);
        
        PreRegisterFarmersBindingStub serviceStub = setupSalesforceAuthentication();
        bulkRegisterFarmers = serviceStub.preRegisterFarmers(bulkRegisterFarmers);
        
        //TODO 4. Save the newIds accepted by Salesforce to the DB and Generate JSON
        String savedFarmerIds = bulkRegisterFarmers.getSavedIds();
        
        String farmerIdsJson = saveNewFarmerIdsAndCreateJson(savedFarmerIds);
                
        return farmerIdsJson;
    }

    private String generateNewFarmerIds(int newIdCount) {
        ArrayList<String> farmerIds = new ArrayList<String>();
        Random rand = new Random();
        
        for (int i=0; i<newIdCount; i++) {
            //TODO May be get the Max value in DB and have a sequential Number
            
            long randomValue = Math.round(rand.nextDouble() * 10000);
            
            //Assumed country is UG for now
            String farmerId = String.format("UG%05d", randomValue);
            if (farmerIds.contains(farmerId)) {
                continue;
            }
            
            //TODO Also Check in DB            
            farmerIds.add(farmerId);
        }
        
        return convertArrayListToCsvString(farmerIds);
    }

    private PreRegisterFarmersBindingStub setupSalesforceAuthentication() throws ServiceException, RemoteException, InvalidIdFault,
            UnexpectedErrorFault, LoginFault {

        PreRegisterFarmersServiceLocator preRegisterFarmerServiceLocator = new PreRegisterFarmersServiceLocator();
        PreRegisterFarmersBindingStub serviceStub = (PreRegisterFarmersBindingStub)preRegisterFarmerServiceLocator.getPreRegisterFarmers();

        // Use soap api to login and get session info
        SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
        soapServiceLocator.setSoapEndpointAddress((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceAddress", ""));
        SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
        LoginResult loginResult = binding.login((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceUsername", ""),
                (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "")
                        + (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceToken", ""));
        SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

        // Share the session info with our webservice
        serviceStub.setHeader("http://soap.sforce.com/schemas/class/PreRegisterFarmers ", "SessionHeader", sessionHeader);
        return serviceStub;
    }
    
    private String saveNewFarmerIdsAndCreateJson(String savedFarmerIds) {
        
        StringBuffer sbFarmerIdsJson = new StringBuffer();
        sbFarmerIdsJson.append("{\"FarmerIds\" : [ ");
        
        if(null == savedFarmerIds || savedFarmerIds.isEmpty()) {
            //TODO Return an empty JSON String
            sbFarmerIdsJson.append("]}");
            return sbFarmerIdsJson.toString();
        }
        
        String[] savedFarmerIdsArray = savedFarmerIds.split(",");
        for (int i=0; i < savedFarmerIdsArray.length; i++) {
            String savedFarmerId = savedFarmerIdsArray[i];
            //TODO Save to the Database
            
            
            //Append to the JSON String
            String fIdJsonPart = "";
            if(i < savedFarmerIdsArray.length - 1) {
                fIdJsonPart = String.format("{\"fId\":\"%s\"},",savedFarmerId);
            }
            else {
                fIdJsonPart = String.format("{\"fId\":\"%s\"}]}",savedFarmerId);
            }
            
            sbFarmerIdsJson.append(fIdJsonPart);
        }
        
        return sbFarmerIdsJson.toString();
    }
    
    private String convertArrayListToCsvString(ArrayList<String> stringArray) {
        StringBuffer sbFinalString = new StringBuffer();
        
        if(null == stringArray || stringArray.isEmpty()) {
            return sbFinalString.toString();
        }
        
        for (int i=0; i < stringArray.size(); i++) {
            if (i < stringArray.size() - 1) {
                sbFinalString.append(stringArray.get(i) + ",");
            }
            else {
                sbFinalString.append(stringArray.get(i));
            }
        }
        
        return sbFinalString.toString();
    }
}
