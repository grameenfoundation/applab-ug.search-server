package applab.search.server;

import java.io.IOException;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.ArrayList;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;

import org.xml.sax.SAXException;

import applab.search.feeds.KeywordEntry;
import applab.search.feeds.ParseFitFeedXml;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;

/**
 * Servlet implementation class UpdateFitFeed
 */
public class UpdateFitFeed extends ApplabServlet {
	private static final long serialVersionUID = 1L;
       
	protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context) 
	        throws ServletException, IOException, SAXException, ParserConfigurationException, ParseException, ClassNotFoundException, SQLException, ServiceException {

	    String writeResult = request.getParameter("write");
	    String manualDate = request.getParameter("manualDate");
	    if (manualDate != null) {
	        manualDate = manualDate + " 23:59:59";
	    }

	    String fitFeedUrl = getServletConfig().getInitParameter("FitFeedUrl");
	    Integer categoryId = Integer.valueOf(getServletConfig().getInitParameter("categoryId"));
	    log("Updating Fit Feed on url " + fitFeedUrl + " with categoryId: " + categoryId + " " + manualDate);
	    ParseFitFeedXml feed = new ParseFitFeedXml(categoryId, fitFeedUrl, manualDate);
	    ArrayList<KeywordEntry> keywords = feed.parseXml();

	    // Loop through the keywords and save them
	    boolean allSaved = true;
	    log("Updating " + keywords.size() + " keywords");
	    if (keywords.size() == 0 || !feed.saveToDatabase()) {
	        allSaved = false;
	    }

	    // Update the last dateRun if all have been saved correctly
	    if (allSaved) {
	        if (writeResult == null) {
	            response.getWriter().write("All keywords have been updated");
	        }
	        log("All keywords updated successfully");
	    }
	    else {
	        if (writeResult == null) {
	            response.getWriter().write("There has been an error. I would investigate if I were you");
	        }
	        log("Some keywords have failed to update. May want check out the issue");
	    }
	}
}
