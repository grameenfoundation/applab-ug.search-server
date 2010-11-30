package applab.search.server;

import java.rmi.RemoteException;
import java.util.HashMap;

import javax.xml.rpc.ServiceException;

import applab.server.SalesforceProxy;

import com.sforce.soap.enterprise.QueryResult;
import com.sforce.soap.enterprise.fault.InvalidFieldFault;
import com.sforce.soap.enterprise.fault.InvalidIdFault;
import com.sforce.soap.enterprise.fault.InvalidQueryLocatorFault;
import com.sforce.soap.enterprise.fault.InvalidSObjectFault;
import com.sforce.soap.enterprise.fault.LoginFault;
import com.sforce.soap.enterprise.fault.MalformedQueryFault;
import com.sforce.soap.enterprise.fault.UnexpectedErrorFault;
import com.sforce.soap.enterprise.sobject.Market__c;

public class SearchSalesforceProxy extends SalesforceProxy {

    public SearchSalesforceProxy() 
            throws ServiceException, InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException {
        super();
    }

    public HashMap<String, String> getRegionMap()
            throws InvalidSObjectFault, MalformedQueryFault, InvalidFieldFault, InvalidIdFault, UnexpectedErrorFault, InvalidQueryLocatorFault, RemoteException {

        HashMap<String, String> regionMap = new HashMap<String, String>();
        StringBuilder queryText = new StringBuilder();
        queryText.append("SELECT ");
        queryText.append("Market__c, Region__c ");
        queryText.append("FROM ");
        queryText.append("Market__c");
        QueryResult query = getBinding().query(queryText.toString());

        if (query.getSize() > 0) {
            for (int i = 0; i < query.getSize(); i++) {
                Market__c market = (Market__c) query.getRecords(i);
                regionMap.put(market.getMarket__c(), market.getRegion__c());
            }
        }
        else {
            return null;
        }
        
        return regionMap;
    }
}
