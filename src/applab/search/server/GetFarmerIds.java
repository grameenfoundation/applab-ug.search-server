package applab.search.server;

import java.io.IOException;
import java.io.PrintWriter;
import java.nio.ByteBuffer;
import java.rmi.RemoteException;
import java.security.SecureRandom;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashSet;
import java.util.Random;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.rpc.ServiceException;

import org.w3c.dom.Document;
import org.w3c.dom.NodeList;

import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.DatabaseTable;
import applab.server.SelectCommand;
import applab.server.ServletRequestContext;
import applab.server.WebAppId;

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
    private static final int FARMER_ID_SET_SIZE = 15;
    private static Connection connection;
    /*/Letters from which random farmer ids shall be generated */
    private static String[] ALPHABET_LETTTERS = { "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "T", "U", "V", "X", "Y",
            "Z" };
    String imei = "";

    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws Exception {
        log("Reached get method for get country code");
        doApplabPost(request, response, context);
        
    }

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException, ClassNotFoundException, SQLException {

        connection = SearchDatabaseHelpers.getWriterConnection();
        try {
            log("Reached post method for Get Farmer Ids");
            imei = request.getHeader(IMEI);
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
        catch (Exception e) {
            log("Error: " + e);
        }
        finally {
        	connection.close();
        }
    }

    
    
    
    
    public long getLongSeed()
    {
    	//Generates a random seed to use with the random number generators
        SecureRandom sec = new SecureRandom();
        byte[] sbuf = sec.generateSeed(8);
        ByteBuffer bb = ByteBuffer.wrap(sbuf);
        return bb.getLong();
    }
    
    
    
    
    private String getFarmerIdsFromSalesforce(String imei, int currentFarmerIdCount) throws RemoteException, ServiceException,
            ClassNotFoundException, SQLException {
        log("Reached Method getFarmerIdsFromSalesforce");

        if (currentFarmerIdCount > FARMER_ID_SET_SIZE / 2) {
            return getEmptyFarmerIdsJson();
        }
        int newIdCount = FARMER_ID_SET_SIZE - currentFarmerIdCount;

        // 1. First generate New Ids and save them
        String generatedFarmerIds = generateNewFarmerIds(newIdCount);

        // 3. Push the IDs to Salesforce as comma seperated
        BulkRegisterFarmers bulkRegisterFarmers = new BulkRegisterFarmers();
        bulkRegisterFarmers.setImei(imei);
        bulkRegisterFarmers.setNewFarmerIds(generatedFarmerIds);

        PreRegisterFarmersBindingStub serviceStub = setupSalesforceAuthentication();
        bulkRegisterFarmers = serviceStub.preRegisterFarmers(bulkRegisterFarmers);

        // 4. Save the newIds accepted by Salesforce to the DB and Generate JSON
        String savedFarmerIds = bulkRegisterFarmers.getSavedIds();
        String farmerIdsJson = saveNewFarmerIdsAndCreateJson(savedFarmerIds);
        return farmerIdsJson;
    }

    /**
     * @return empty json string of the form {"FarmerIds" : [] }
     */
    private String getEmptyFarmerIdsJson() {
        return String.format("{\"FarmerIds\" : []}");
    }

    /**
     * This generates random Ids, currently this has a limit of about 1.8 million different farmers To increase this,
     * remove the fixed 'U' prefix
     * 
     * @param newIdCount
     * @return
     * @throws ClassNotFoundException
     * @throws SQLException
     */
    private String generateNewFarmerIds(int newIdCount) throws ClassNotFoundException, SQLException {

        /* Set to show generated Ids and check whether they exist in the database as alreay generated */
        HashSet<String> farmerIds = new HashSet<String>();
        Random rand = new Random(getLongSeed());
       
        while (farmerIds.size() < newIdCount) {        	
            long randomNumber = Math.round(rand.nextDouble() * 100000 - 1);
        	//long randomNumber = new Long( GenerateRandomPin(5));
            int randomLetter = rand.nextInt(ALPHABET_LETTTERS.length);

            // Assumed country is Uganda for now ??
            String farmerId = String.format("U%s%05d", ALPHABET_LETTTERS[randomLetter], randomNumber);
            log("Check if Id: " + farmerId + " is already in database");
            if (isAlreadyInDatabase(farmerId)) {
                log("ID is already in database");
                continue;
            }
            log("id added to collection: " + farmerId);
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

    private String saveNewFarmerIdsAndCreateJson(String savedFarmerIds) throws ClassNotFoundException, SQLException {

        HashSet<String> savedFarmerIdSet = new HashSet<String>();
        StringBuffer sbFarmerIdsJson = new StringBuffer();
        sbFarmerIdsJson.append("{\"FarmerIds\" : [ ");

        if (null == savedFarmerIds || savedFarmerIds.isEmpty()) {
            // TODO Return an empty JSON String
            sbFarmerIdsJson.append("]}");
            return sbFarmerIdsJson.toString();
        }

        String[] savedFarmerIdsArray = savedFarmerIds.split(",");
        for (int i = 0; i < savedFarmerIdsArray.length; i++) {
            String savedFarmerId = savedFarmerIdsArray[i];
            savedFarmerIdSet.add(savedFarmerId);

            // Append to the JSON String
            String fIdJsonPart = "";
            if (i < savedFarmerIdsArray.length - 1) {
                fIdJsonPart = String.format("{\"fId\":\"%s\"},", savedFarmerId);
            }
            else {
                fIdJsonPart = String.format("{\"fId\":\"%s\"}]}", savedFarmerId);
            }

            sbFarmerIdsJson.append(fIdJsonPart);
        }
        // save Ids to database
        saveFarmerIdsToDatabase(savedFarmerIdSet);
        log("Ids returned : " + savedFarmerIdSet);
        return sbFarmerIdsJson.toString();
    }

    private String convertArrayListToCsvString(HashSet<String> stringSet) {
        StringBuffer sbFinalString = new StringBuffer();
        String[] stringArray = stringSet.toArray(new String[0]);
        if (null == stringArray || stringArray.length == 0) {
            return sbFinalString.toString();
        }

        for (int i = 0; i < stringArray.length; i++) {
            if (i < stringArray.length - 1) {
                sbFinalString.append(stringArray[i] + ",");
            }
            else {
                sbFinalString.append(stringArray[i]);
            }
        }
        return sbFinalString.toString();
    }

    private boolean isAlreadyInDatabase(String farmerId) throws ClassNotFoundException, SQLException {
       SelectCommand selectCommand = new SelectCommand(DatabaseTable.FarmerId);
        selectCommand.addField("farmerids.farmer_id", "farmerId");
        selectCommand.where("farmerids.farmer_id = '" + farmerId + "'");
        log("Built select commmand");
        java.sql.Statement statement = connection.createStatement();
        boolean result  = true;
        try
        {
        	ResultSet resultSet = statement.executeQuery(selectCommand.getCommandText());
        	if(resultSet.first())
        	{
        		result = true;
        	}
        	else
        	{
        		result = false;
        	}
        	statement.close();
        }
        catch(Exception e)
        {
        	log("Error checking if id is in use "+e.getMessage());
        	e.printStackTrace(System.err);
        }
        //ResultSet resultSet = selectCommand.execute();
        //boolean result = resultSet.first();
        //selectCommand.dispose();
        //log("Built select commmand");

        // returns true if there is a first row which in escence mean there is a matching farmer id, esle returns false
        return result;
    }

    private void saveFarmerIdsToDatabase(HashSet<String> farmerIds) throws ClassNotFoundException, SQLException {
    	connection = SearchDatabaseHelpers.getWriterConnection();
        connection.setAutoCommit(false);
        StringBuilder commandText = new StringBuilder();
        commandText.append("INSERT INTO farmerids ");
        commandText.append("(farmer_id, imei");
        commandText.append(") values (?, ?)");
        PreparedStatement submissionStatement = connection.prepareStatement(commandText.toString());
        log("Farmer Ids to add to database " + farmerIds.size());
        for (String farmerId : farmerIds) {
            submissionStatement.setString(1, farmerId);
            submissionStatement.setString(2, imei);
            submissionStatement.addBatch();
        }
        
        try {
            log("Prepared statement: " + submissionStatement.toString());
            submissionStatement.executeBatch();
        }
        catch (SQLException e) {
            log("Error " + e);
            e.printStackTrace();
            connection.rollback();
            connection.setAutoCommit(true);
            submissionStatement.close();
        }
        connection.commit();
        connection.setAutoCommit(true);
        submissionStatement.close();
    }

}
