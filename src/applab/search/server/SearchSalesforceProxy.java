package applab.search.server;

import applab.server.SalesforceProxy;
import com.sforce.soap.enterprise.QueryResult;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.enterprise.fault.InvalidFieldFault;
import com.sforce.soap.enterprise.fault.InvalidIdFault;
import com.sforce.soap.enterprise.fault.InvalidQueryLocatorFault;
import com.sforce.soap.enterprise.fault.InvalidSObjectFault;
import com.sforce.soap.enterprise.fault.LoginFault;
import com.sforce.soap.enterprise.fault.MalformedQueryFault;
import com.sforce.soap.enterprise.fault.UnexpectedErrorFault;
import com.sforce.soap.enterprise.sobject.Attachment;
import com.sforce.soap.enterprise.sobject.Country__c;
import com.sforce.soap.enterprise.sobject.Market__c;
import com.sforce.soap.enterprise.sobject.Person__c;
import java.rmi.RemoteException;
import java.util.HashMap;
import javax.xml.rpc.ServiceException;

public class SearchSalesforceProxy extends SalesforceProxy
{
  public SearchSalesforceProxy()
    throws ServiceException, InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException
  {
  }

  public HashMap<String, String> getRegionMap()
    throws InvalidSObjectFault, MalformedQueryFault, InvalidFieldFault, InvalidIdFault, UnexpectedErrorFault, InvalidQueryLocatorFault, RemoteException
  {
    HashMap regionMap = new HashMap();
    StringBuilder queryText = new StringBuilder();
    queryText.append("SELECT ");
    queryText.append("Market__c, Region__c ");
    queryText.append("FROM ");
    queryText.append("Market__c");
    QueryResult query = getBinding().query(queryText.toString());

    if (query.getSize() > 0) {
      for (int i = 0; i < query.getSize(); i++) {
        Market__c market = (Market__c)query.getRecords(i);
        regionMap.put(market.getMarket__c(), market.getRegion__c());
      }
    }
    else {
      return null;
    }

    return regionMap;
  }

  public String getCountryCode(String imei)
    throws InvalidSObjectFault, MalformedQueryFault, InvalidFieldFault, InvalidIdFault, UnexpectedErrorFault, InvalidQueryLocatorFault, RemoteException
  {
    StringBuilder queryText = new StringBuilder();
    queryText.append("SELECT ");
    queryText.append("p.Country__r.ISO_Standard_Code__c ");
    queryText.append("FROM ");
    queryText.append("Person__c p ");
    queryText.append("WHERE ");
    queryText.append("p.Handset__r.IMEI__c = '");
    queryText.append(imei);
    queryText.append("'");

    QueryResult query = getBinding().query(queryText.toString());

    if (query.getSize() > 0) {
      Person__c person = (Person__c)query.getRecords(0);
      return person.getCountry__r().getISO_Standard_Code__c();
    }

    return null;
  }

  public boolean checkIfIsCkw(String imei)
    throws Exception
  {
    StringBuilder queryText = new StringBuilder();
    queryText.append("SELECT ");
    queryText.append("Id");
    queryText.append("FROM ");
    queryText.append("Ckw__c ");
    queryText.append("WHERE ");
    queryText.append("Person__r.Handset__r.IMEI__c = '");
    queryText.append(imei);
    queryText.append("'");

    QueryResult query = getBinding().query(queryText.toString());

    return query.getSize() > 0;
  }

  public Attachment getAttachement(String id)
    throws InvalidSObjectFault, MalformedQueryFault, InvalidFieldFault, InvalidIdFault, UnexpectedErrorFault, InvalidQueryLocatorFault, RemoteException
  {
    StringBuilder queryText = new StringBuilder();
    queryText.append("SELECT ");
    queryText.append("Id, Body ");
    queryText.append("FROM ");
    queryText.append("Attachment ");
    queryText.append("WHERE ");
    queryText.append("Id = '");
    queryText.append(id);
    queryText.append("'");

    QueryResult query = getBinding().query(queryText.toString());
    if (query.getSize() > 0) {
      Attachment attachment = (Attachment)query.getRecords(0);
      return attachment;
    }
    return null;
  }

  public boolean checkIfImeiIsForPersonInCountryCode(String imei, String countryCode) throws InvalidSObjectFault, MalformedQueryFault, InvalidFieldFault, InvalidIdFault, UnexpectedErrorFault, InvalidQueryLocatorFault, RemoteException
  {
    try {
      StringBuilder queryText = new StringBuilder();
      queryText.append("SELECT ");
      queryText.append("Id ");
      queryText.append("FROM ");
      queryText.append("Person__c ");
      queryText.append("WHERE ");
      queryText.append("Handset__r.IMEI__c = '");
      queryText.append(imei);
      queryText.append("' AND ");
      queryText.append("Country__r.ISO_Standard_Code__c = '");
      queryText.append(countryCode);
      queryText.append("'");

      QueryResult query = getBinding().query(queryText.toString());

      return query.getSize() > 0;
    }
    catch (Exception ex)
    {
    }

    return false;
  }
}