package applab.search.server.test;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import junit.framework.Assert;

import org.junit.Before;
import org.junit.Test;
import com.sforce.soap.enterprise.QueryResult;
import com.sforce.soap.enterprise.SaveResult;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.enterprise.sobject.CKW__c;
import com.sforce.soap.enterprise.sobject.Farmer__c;
import com.sforce.soap.enterprise.sobject.Person__c;
import com.sforce.soap.enterprise.sobject.Phone__c;

import applab.search.server.Search;
import applab.server.ApplabConfiguration;
import applab.server.DatabaseTable;
import applab.server.SalesforceProxy;
import applab.server.SelectCommand;
import applab.server.SqlImplementation;
import applab.server.test.RemoteSqlImplementation;
import applab.server.test.ServerTestCase;

public class TestSearch extends ServerTestCase {
    // Binding
    private SoapBindingStub binding;
    
    @Before
    public void setUp() throws Exception {
        super.setUp();
        binding = SalesforceProxy.createBinding();
    }
    
    /**
     * Use to test that the mockRequest returns non-empty content.
     * 
     * @throws SQLException
     * @throws ClassNotFoundException
     */
    @Test
    public void testResponseIsValid() {
        try {
            // Get one valid keyword from the db and get its response
            String keywordTableName = DatabaseTable.Keyword.getTableName();
            String categoryTableName = DatabaseTable.Category.getTableName();
            SelectCommand select = new SelectCommand(DatabaseTable.Keyword);
            select.addField(keywordTableName + ".keyword");
            select.addField(keywordTableName + ".content");
            select.addField(categoryTableName + ".name");
            select.whereEquals(keywordTableName + ".isDeleted","0");
            select.whereEquals(categoryTableName + ".isDeleted","0");
            select.innerJoin(DatabaseTable.Category, keywordTableName + ".categoryId = " + categoryTableName + ".id");
            select.orderBy("RAND()");
            select.limit(1);
            ResultSet resultSet = select.execute();
            select.dispose();

            resultSet.first();
            String keyword = resultSet.getString("keyword");
            Assert.assertFalse(keyword.isEmpty());

            HashMap<String, String> record = Search.getContent(resultSet.getString("keyword"));
            Assert.assertNotNull(record); // Shouldn't be null
            Assert.assertTrue(record.get("content").length() > 0); // Content should be non zero length
            Assert.assertEquals(record.get("content"), resultSet.getString("content")); // Content should be the
                                                                                              // same
        }
        catch (Exception e) {
            e.printStackTrace();
            Assert.fail(e.toString());
        }
    }

    /*
     * Test that we can actually register a hit in SF
     */
    @Test
    public void testLogSearchRequest() throws Exception {
        // Setup SF objects

        try {
            // Create handset
            Phone__c phone = new Phone__c();
            phone.setSerial_Number__c("TestSearchSerialNumber"); // This is required too
            phone.setIMEI__c("TestSearchIMEI");
            SaveResult[] phoneSaveResult = binding.create(new Phone__c[] { phone });

            if (!phoneSaveResult[0].isSuccess()) {
                throw new Exception("Test Failed: Failed to save Phone!");
            }
            else {
                trackCreatedSalesforceObject(phoneSaveResult[0].getId());
            }

            // Create interviewer
            Person__c person = new Person__c();
            person.setFirst_Name__c("MyTestCkwFirstName");
            person.setLast_Name__c("MyTestCkwLastName");
            person.setHandset__c(phoneSaveResult[0].getId());
            SaveResult[] personSaveResult = binding.create(new Person__c[] { person });

            if (!personSaveResult[0].isSuccess()) {
                throw new Exception("Test Failed: Failed to save Person!");
            }
            else {
                trackCreatedSalesforceObject(personSaveResult[0].getId());
            }
            
            CKW__c ckw = new CKW__c();
            ckw.setPerson__c(personSaveResult[0].getId());
            SaveResult[] ckwSaveResult = binding.create(new CKW__c[] { ckw });
            
            if (!ckwSaveResult[0].isSuccess()) {
                throw new Exception("Test Failed: Failed to save CKW!");
            }
            else {
                trackCreatedSalesforceObject(ckwSaveResult[0].getId());
            }
            
            // Create interviewee
            Person__c farmerPersonObject = new Person__c();
            farmerPersonObject.setFirst_Name__c("MyTestFarmerFirstName");
            farmerPersonObject.setLast_Name__c("MyTestFarmerLastName");
            SaveResult[] farmerPersonObjectSaveResult = binding.create(new Person__c[] { farmerPersonObject });
            
            if (!farmerPersonObjectSaveResult[0].isSuccess()) {
                throw new Exception("Test Failed: Failed to save Farmer Person Object!");
            }
            else {
                trackCreatedSalesforceObject(farmerPersonObjectSaveResult[0].getId());
            }
            
            Farmer__c farmer = new Farmer__c();
            farmer.setName("MyTestFarmerId");
            farmer.setPerson__c(farmerPersonObjectSaveResult[0].getId());
            SaveResult[] farmerSaveResult = binding.create(new Farmer__c[] { farmer } );
            
            if (!farmerSaveResult[0].isSuccess()) {
                throw new Exception("Test Failed: Failed to save Farmer!");
            }
            else {
                trackCreatedSalesforceObject(farmerSaveResult[0].getId());
            }
            

            // Run a query that should register a hit for those objects
            if (ApplabConfiguration.useRemoteDatabase()) {
                SqlImplementation.setCurrent(new RemoteSqlImplementation());
            }
            HashMap<String, String> content = Search.getContent("MyTestKeyword");
            Search.logSearchRequest("TestSearchIMEI", "MyTestFarmerId", "MyTestKeyword", content, "", false, "2010-09-22 00:00:00");
    
            // Check that a hit is registered and is linked to right ckw and farmer
            QueryResult logQuery = binding.query("Select Id FROM Search_Log__c WHERE Interviewer__c = '" + personSaveResult[0].getId() + "' and Interviewee__c = '" + farmerPersonObjectSaveResult[0].getId() + "'");
            Assert.assertTrue(logQuery.getSize() > 0);
           
            // Store for clean up 
            trackCreatedSalesforceObject(logQuery.getRecords(0).getId());
            
            // Check that if only first and last name are given, a hit is still linked to the correct farmer
            Search.logSearchRequest("TestSearchIMEI", "", "MyTestKeyword", content, "", false, "2010-09-22 00:00:00");
            QueryResult logQuery2 = binding.query("Select Id FROM Search_Log__c WHERE Interviewer__c = '" + personSaveResult[0].getId() + "' and Interviewee__c = '" + farmerPersonObjectSaveResult[0].getId() + "'");
            Assert.assertTrue(logQuery2.getSize() == (logQuery.getSize() + 1)); // Now two hits for this farmer
            
            // Store for clean up
            for(int counter = 0; counter < logQuery2.getSize(); counter ++) {
                trackCreatedSalesforceObject(logQuery2.getRecords(counter).getId());
            }
            
            // TODO: Check that if no farmer is given, a hit is still registered, and an anonymous farmer is created
            
            // TODO: Check that if an unknown handset id is given, a hit is still registered, and an anonymous ckw
        }
        catch (Exception e) {
            e.printStackTrace();
            Assert.fail(e.toString());
        }
    }
}

