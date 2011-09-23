/**
 *
 * Copyright (c) 2011 AppLab, Grameen Foundation
 *
 **/

package applab.search.server;

import java.io.IOException;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.concurrent.Executor;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.RejectedExecutionException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;
import javax.xml.transform.TransformerException;
import org.w3c.dom.Document;
import org.xml.sax.SAXException;
import applab.search.feeds.ParseIconWeatherFeedXml;
import applab.search.feeds.WeatherConditionsKeyword;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.XmlHelpers;

/**
 * Servlet implementation class UpdateIconWeatherFeed.
 * 
 */
public class UpdateIconWeatherFeed extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static final ExecutorService exec = Executors.newSingleThreadExecutor();

    /***
     * Update weather for a weather update request body of the form <?xml version="1.0"?> <locations> <location>
     * <location_id>100229362</location_id> <subcounty_name>Benet</subcounty_name> <region_name>Eastern
     * Uganda</region_name> <district_name>Kapchorwa</district_name> </location> <location>
     * <location_id>100229362</location_id> <subcounty_name>Benet</subcounty_name> <region_name>Eastern
     * Uganda</region_name> <district_name>Kapchorwa</district_name> </location> . . . </locations>
     */
    @Override
    protected void doApplabPost(HttpServletRequest request,
                                HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, SAXException, ParserConfigurationException, ParseException, ClassNotFoundException,
            SQLException, ServiceException, TransformerException {

        final Integer categoryId = Integer.valueOf(getServletConfig().getInitParameter("categoryId"));

        String iconFeedUrl = getServletConfig().getInitParameter("IconFeedUrl");
        final String iconWeatherFeedUrl = iconFeedUrl + "3?locationid=";
        final String iconWeatherForecastFeedUrl = iconFeedUrl + "2?locationid=";

        log("Updating Icon Weather Feed on URL " + iconFeedUrl + " with categoryId = " + categoryId);

        final Document requestXml = XmlHelpers.parseXml(request.getReader());

        // Handover processing to another thread to allow servlet to return to client
        Runnable task = new Runnable() {
            public void run() {
                ParseIconWeatherFeedXml feed = new ParseIconWeatherFeedXml(categoryId, iconWeatherFeedUrl, iconWeatherForecastFeedUrl);

                boolean allSaved = true;
                try {
                    if (feed.parseWeatherRequest(requestXml)) {
                        ArrayList<WeatherConditionsKeyword> keywords = feed.parseIconWeather();

                        // Loop through the keywords and save them
                        log("Updating " + keywords.size() + " keywords");
                        if (keywords.size() == 0 || !feed.saveToDatabase()) {
                            allSaved = false;
                        }
                    }
                    else {
                        allSaved = false;
                    }
                }
                catch (Exception e) {
                    log(e.getMessage());
                    allSaved = false;
                }

                if (allSaved) {
                    log("All keywords updated successfully");
                }
                else {
                    log("Some keywords have failed to update. May want check out the issue");
                }
            }
        };

        try {
            exec.execute(task);
        }
        catch (RejectedExecutionException e) {
            log("Keyword Processing Task rejected", e);
        }

        response.setStatus(HttpServletResponse.SC_ACCEPTED);
    }
}
