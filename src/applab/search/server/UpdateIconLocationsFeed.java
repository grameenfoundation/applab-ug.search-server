/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.server;

import applab.Location;
import applab.search.feeds.ParseIconLocationsFeedXml;
import applab.server.ApplabServlet;
import applab.server.DatabaseHelpers;
import applab.server.ServletRequestContext;
import applab.server.XmlHelpers;
import java.io.IOException;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import javax.servlet.ServletContext;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;
import javax.xml.transform.TransformerException;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.xml.sax.SAXException;

public class UpdateIconLocationsFeed extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    public static final String REQUEST_ELEMENT_NAME = "locations";
    public static final String RESPONSE_ELEMENT_NAME = "locations";
    public static final String ROW_ELEMENT_NAME = "location";
    public static final String LOCATION_ID_ELEMENT_NAME = "location_id";
    public static List<LocationRequest> locationRequests;

    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, SAXException, ParserConfigurationException, ParseException, ClassNotFoundException,
            SQLException, ServiceException, TransformerException {
        String iconLocationIdsFeedUrl = request.getSession().getServletContext().getRealPath("/") + "WEB-INF/IconLocationIds.xml";
        locationRequests = new ArrayList();
        log("Updating Icon Location Ids : " + DatabaseHelpers.formatDateTime(new Date()));
        ParseIconLocationsFeedXml feed = new ParseIconLocationsFeedXml(iconLocationIdsFeedUrl);
        feed.parseLocationsXml();
        Document requestXml = XmlHelpers.parseXml(request.getReader());
        parseRequest(requestXml);
        response.setStatus(200);
        context.writeXmlHeader();
        context.writeStartElement("locations");
        for (LocationRequest locationRequest : locationRequests) {
            if ((locationRequest.getLatitude() == null) || (locationRequest.getLongitude() == null)) {
                continue;
            }
            Location location = feed.findClosestLocationId(locationRequest.getLatitude(), locationRequest.getLongitude());
            if ((location == null) || (location.getLocationId() == null) || (location.getLocationId() == ""))
                continue;
            context.writeStartElement("location");
            context.writeStartElement(LocationRequestElement.subcounty_id.toString());
            context.print(locationRequest.getSubcountyId());
            context.writeEndElement();
            context.writeStartElement("location_id");
            context.print(location.getLocationId());
            context.writeEndElement();
            context.writeEndElement();
        }
        context.writeEndElement();
        context.println("");
    }

    private static void parseRequest(Document requestXml) throws ParseException {
        assert (requestXml != null);
        requestXml.normalizeDocument();

        Element rootNode = requestXml.getDocumentElement();

        for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode.getNextSibling()) {
            if ((childNode.getNodeType() != 1) || (!childNode.getLocalName().equals("location")))
                continue;
            parseLocationRequestItem((Element)childNode);
        }
    }

    private static void parseLocationRequestItem(Element itemNode) throws ParseException {
        String subcountyId = null;
        String longitude = null;
        String latitude = null;

        for (Node childNode = itemNode.getFirstChild(); childNode != null; childNode = childNode.getNextSibling()) {
            if (childNode.getNodeType() != 1)
                continue;
            LocationRequestElement locationRequestElement = LocationRequestElement.valueOf(childNode.getLocalName());

            switch (locationRequestElement) {
                case latitude:
                    subcountyId = XmlHelpers.parseCharacterData((Element)childNode);
                    break;
                case longitude:
                    longitude = XmlHelpers.parseCharacterData((Element)childNode);
                    break;
                case subcounty_id:
                    latitude = XmlHelpers.parseCharacterData((Element)childNode);
                    break;
            }

        }

        locationRequests.add(new LocationRequest(subcountyId, longitude, latitude));
    }

    static class LocationRequest {
        private String subcountyId;
        private String longitude;
        private String latitude;

        public LocationRequest(String subcountyId, String longitude, String latitude) {
            this.subcountyId = subcountyId;
            this.longitude = longitude;
            this.latitude = latitude;
        }

        public String getSubcountyId() {
            return this.subcountyId;
        }

        public String getLongitude() {
            return this.longitude;
        }

        public String getLatitude() {
            return this.latitude;
        }
    }

    private static enum LocationRequestElement {
        subcounty_id,
        longitude,
        latitude;
    }
}