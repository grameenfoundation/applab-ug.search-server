package applab.search.server;

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
import java.io.IOException;
import java.io.PrintWriter;
import java.rmi.RemoteException;
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
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

public class GetFarmerIds extends ApplabServlet
{
  private static final long serialVersionUID = 1L;
  private static final String IMEI = "x-Imei";
  private static final String CURRENT_FARMER_ID_COUNT = "currentFarmerIdCount";
  private static final int FARMER_ID_SET_SIZE = 15;
  private static String[] ALPHABET_LETTTERS = { "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "T", "U", "V", "X", "Y", 
    "Z" };

  String imei = "";

  protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws Exception
  {
    log("Reached get method for get country code");
    doApplabPost(request, response, context);
  }

  protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws ServletException, IOException, ServiceException
  {
    try
    {
      log("Reached post method for Get Farmer Ids");
      this.imei = request.getHeader("x-Imei");
      log("x-Imei: " + this.imei);
      Document requestXml = context.getRequestBodyAsXml();
      NodeList nodeList = requestXml.getElementsByTagName("currentFarmerIdCount");
      String farmerIdString = nodeList.item(0).getTextContent();
      log("Farmer Id Count : " + farmerIdString);
      int farmerIdCount = Integer.parseInt(farmerIdString);
      log("Current Farmer Id Count: " + farmerIdCount);

      String jsonResult = getFarmerIdsFromSalesforce(this.imei, farmerIdCount);

      PrintWriter out = response.getWriter();
      out.println(jsonResult);
      log("Finished sending new Farmer Ids");
    }
    catch (Exception e) {
      log("Error: " + e);
    }
  }

  private String getFarmerIdsFromSalesforce(String imei, int currentFarmerIdCount) throws RemoteException, ServiceException, ClassNotFoundException, SQLException
  {
    log("Reached Method getFarmerIdsFromSalesforce");

    if (currentFarmerIdCount > 7) {
      return getEmptyFarmerIdsJson();
    }
    int newIdCount = 15 - currentFarmerIdCount;

    String generatedFarmerIds = generateNewFarmerIds(newIdCount);

    BulkRegisterFarmers bulkRegisterFarmers = new BulkRegisterFarmers();
    bulkRegisterFarmers.setImei(imei);
    bulkRegisterFarmers.setNewFarmerIds(generatedFarmerIds);

    PreRegisterFarmersBindingStub serviceStub = setupSalesforceAuthentication();
    bulkRegisterFarmers = serviceStub.preRegisterFarmers(bulkRegisterFarmers);

    String savedFarmerIds = bulkRegisterFarmers.getSavedIds();

    String farmerIdsJson = saveNewFarmerIdsAndCreateJson(savedFarmerIds);

    return farmerIdsJson;
  }

  private String getEmptyFarmerIdsJson()
  {
    return String.format("{\"FarmerIds\" : []}", new Object[0]);
  }

  private String generateNewFarmerIds(int newIdCount)
    throws ClassNotFoundException, SQLException
  {
    HashSet farmerIds = new HashSet();
    Random rand = new Random(1000L);

    while (farmerIds.size() < newIdCount)
    {
      long randomNumber = Math.round(rand.nextDouble() * 100000.0D - 1.0D);
      int randomLetter = rand.nextInt(ALPHABET_LETTTERS.length);

      String farmerId = String.format("U%s%05d", new Object[] { ALPHABET_LETTTERS[randomLetter], Long.valueOf(randomNumber) });
      log("Check if Id: " + farmerId + " is already in database");
      if (isAlreadyInDatabase(farmerId)) {
        log("ID is already in database");
      }
      else {
        log("id added to collection: " + farmerId);
        farmerIds.add(farmerId);
      }
    }
    return convertArrayListToCsvString(farmerIds);
  }

  private PreRegisterFarmersBindingStub setupSalesforceAuthentication()
    throws ServiceException, RemoteException, InvalidIdFault, UnexpectedErrorFault, LoginFault
  {
    PreRegisterFarmersServiceLocator preRegisterFarmerServiceLocator = new PreRegisterFarmersServiceLocator();
    PreRegisterFarmersBindingStub serviceStub = (PreRegisterFarmersBindingStub)preRegisterFarmerServiceLocator.getPreRegisterFarmers();

    SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
    soapServiceLocator.setSoapEndpointAddress((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceAddress", ""));
    SoapBindingStub binding = (SoapBindingStub)soapServiceLocator.getSoap();
    LoginResult loginResult = binding.login((String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceUsername", ""), 
      (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "") + 
      (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforceToken", ""));
    SessionHeader sessionHeader = new SessionHeader(loginResult.getSessionId());

    serviceStub.setHeader("http://soap.sforce.com/schemas/class/PreRegisterFarmers ", "SessionHeader", sessionHeader);
    return serviceStub;
  }

  private String saveNewFarmerIdsAndCreateJson(String savedFarmerIds) throws ClassNotFoundException, SQLException
  {
    HashSet savedFarmerIdSet = new HashSet();
    StringBuffer sbFarmerIdsJson = new StringBuffer();
    sbFarmerIdsJson.append("{\"FarmerIds\" : [ ");

    if ((savedFarmerIds == null) || (savedFarmerIds.isEmpty()))
    {
      sbFarmerIdsJson.append("]}");
      return sbFarmerIdsJson.toString();
    }

    String[] savedFarmerIdsArray = savedFarmerIds.split(",");
    for (int i = 0; i < savedFarmerIdsArray.length; i++) {
      String savedFarmerId = savedFarmerIdsArray[i];

      String fIdJsonPart = "";
      if (i < savedFarmerIdsArray.length - 1) {
        fIdJsonPart = String.format("{\"fId\":\"%s\"},", new Object[] { savedFarmerId });
      }
      else {
        fIdJsonPart = String.format("{\"fId\":\"%s\"}]}", new Object[] { savedFarmerId });
      }

      sbFarmerIdsJson.append(fIdJsonPart);
    }

    saveFarmerIdsToDatabase(savedFarmerIdSet);

    return sbFarmerIdsJson.toString();
  }

  private String convertArrayListToCsvString(HashSet<String> stringSet) {
    StringBuffer sbFinalString = new StringBuffer();
    String[] stringArray = (String[])stringSet.toArray(new String[0]);
    if ((stringArray == null) || (stringArray.length == 0)) {
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
    ResultSet resultSet = selectCommand.execute();
    log("Built select commmand");

    return resultSet.first();
  }

  private void saveFarmerIdsToDatabase(HashSet<String> farmerIds) throws ClassNotFoundException, SQLException {
    Connection connection = SearchDatabaseHelpers.getWriterConnection();
    connection.setAutoCommit(false);
    StringBuilder commandText = new StringBuilder();
    commandText.append("INSERT INTO farmerids ");
    commandText.append("(farmer_id, imei");
    commandText.append(") values (?, ?)");
    PreparedStatement submissionStatement = connection.prepareStatement(commandText.toString());
    log("Farmer Ids to add to database " + farmerIds.size());
    for (String farmerId : farmerIds) {
      submissionStatement.setString(1, farmerId);
      submissionStatement.setString(2, this.imei);
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