/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.server;

import applab.search.feeds.ParseIconWeatherFeedXml;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.XmlHelpers;
import java.io.IOException;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.RejectedExecutionException;
import javax.servlet.ServletConfig;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;
import javax.xml.transform.TransformerException;
import org.w3c.dom.Document;
import org.xml.sax.SAXException;

public class UpdateIconWeatherFeed extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static final ExecutorService exec = Executors.newSingleThreadExecutor();

    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, SAXException, ParserConfigurationException, ParseException, ClassNotFoundException,
            SQLException, ServiceException, TransformerException {
        Integer categoryId = Integer.valueOf(getServletConfig().getInitParameter("categoryId"));
        String iconFeedUrl = getServletConfig().getInitParameter("IconFeedUrl");
        String iconWeatherFeedUrl = iconFeedUrl + "3?locationid=";
        String iconWeatherForecastFeedUrl = iconFeedUrl + "2?locationid=";
        log("Updating Icon Weather Feed on URL " + iconFeedUrl + " with categoryId = " + categoryId);
        Document requestXml = XmlHelpers.parseXml(request.getReader());
        Runnable task = new Runnable(categoryId, iconWeatherFeedUrl, iconWeatherForecastFeedUrl, requestXml) {
            public void run() {
                ParseIconWeatherFeedXml feed = new ParseIconWeatherFeedXml(this.val$categoryId, this.val$iconWeatherFeedUrl,
                        this.val$iconWeatherForecastFeedUrl);
                boolean allSaved = true;
                try {
                    if (feed.parseWeatherRequest(this.val$requestXml)) {
                        ArrayList keywords = feed.parseIconWeather();
                        UpdateIconWeatherFeed.this.log("Updating " + keywords.size() + " keywords");
                        if ((keywords.size() == 0) || (!feed.saveToDatabase())) {
                            allSaved = false;
                        }
                    }
                    else {
                        allSaved = false;
                    }
                }
                catch (Exception e) {
                    UpdateIconWeatherFeed.this.log(e.getMessage());
                    allSaved = false;
                }

                if (allSaved) {
                    UpdateIconWeatherFeed.this.log("All keywords updated successfully");
                }
                else
                    UpdateIconWeatherFeed.this.log("Some keywords have failed to update. May want check out the issue");
            }
        };
        try {
            exec.execute(task);
        }
        catch (RejectedExecutionException e) {
            log("Keyword Processing Task rejected", e);
        }

        response.setStatus(202);
    }
}