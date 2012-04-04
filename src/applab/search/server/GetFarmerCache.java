package applab.search.server;

import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;

import java.io.IOException;
import java.io.PrintWriter;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;

import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

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
     * @param imei
     * @param lastUpdateDate
     * @return
     */
    private String getFarmerCacheFromSalesforce(String imei, String lastUpdateDate) {
        return "{\"Farmers\" : [ {\"Id\" : \"234234311f454\", \"FId\" : \"DF23242\",\"FName\" : \"Moses\", \"MName\" : \"Oscar\", \"LName\" : \"MUHAHALA\",\"Dob\" : \"1980-08-20 12:00:00\", \"PName\" : \"Oloo\"}," +
                " {\"Id\" : \"234234311f454\", \"FId\" : \"DF23242\",\"FName\" : \"Moses\", \"MName\" : \"Oscar\", \"LName\" : \"MUHAHALA\",\"Dob\" : \"1980-08-20 12:00:00\", \"PName\" : \"Oloo\"}], \"LastUpdatedDate\": \"2012-04-03 19:00:00\"}";
    }

}
