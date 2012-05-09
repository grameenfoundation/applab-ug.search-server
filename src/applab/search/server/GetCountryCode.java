package applab.search.server;

import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;

import java.io.IOException;
import java.io.PrintWriter;
import java.rmi.RemoteException;
import java.util.Random;

import javax.servlet.Servlet;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;

import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

import com.sforce.soap.enterprise.fault.InvalidFieldFault;
import com.sforce.soap.enterprise.fault.InvalidIdFault;
import com.sforce.soap.enterprise.fault.InvalidQueryLocatorFault;
import com.sforce.soap.enterprise.fault.InvalidSObjectFault;
import com.sforce.soap.enterprise.fault.MalformedQueryFault;
import com.sforce.soap.enterprise.fault.UnexpectedErrorFault;


public class GetCountryCode extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static final String IMEI = "x-Imei";
    private static final String COUNTRY_CODE = "CountryCode";
    
    @Override
    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {
	    log("Reached get method for get country code");
        doApplabPost(request, response, context);
	}
    
    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, ServiceException {
        
        log("Reached post method for get coutry code");
        String imei = request.getHeader(IMEI);
        log("x-Imei: " + imei);       
                    
        // make Salesforce call
        String countryCode = getCountryCodeFromSalesforce(imei);
        
        PrintWriter out = response.getWriter();
        out.println(countryCode);
        log("Finished sending country code");        
    }
    
    /**
     * Stub for Saleforce method
     * @param imei    
     * @return
     */
    private String getCountryCodeFromSalesforce(String imei) {        
        
        try {
            SearchSalesforceProxy searchSaleforceProxy = new SearchSalesforceProxy();
            String countryCode = searchSaleforceProxy.getCountryCode(imei);
            log("Country code for IMEI " + imei + " is: " + countryCode);
            return countryCode;
        }
        catch (Exception e) {
            log("Failed to get county code from salesforce");
            e.printStackTrace();
        }
        return null;
        
    }
    


}
