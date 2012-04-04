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
public class GetFarmerIds extends ApplabServlet {
	private static final long serialVersionUID = 1L;
	private static final String IMEI = "x-Imei";
	private static final String CURRENT_FARMER_ID_COUNT = "currentFarmerIdCount";

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
            int farmerIdCount = Integer.parseInt(nodeList.item(0).getTextContent());
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
     * @param imei
     * @param currentFarmerIdCount
     * @return
     */
    private String getFarmerIdsFromSalesforce(String imei, int currentFarmerIdCount) {
        return "{\"FarmerIds\" : [ {\"Id\" : \"234234311f454\", \"FId\" : \"DF23242\"}, {\"Id\" : \"23428s11f454\", \"FId\" : \"CQ0000\"}]}";
    }

}
