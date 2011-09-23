/**
 *
 * Copyright (c) 2011 AppLab, Grameen Foundation
 *
 **/
package applab.search.server;

import java.io.IOException;
import java.sql.SQLException;
import java.text.ParseException;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;
import javax.xml.transform.TransformerException;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.XmlHelpers;
import java.util.ArrayList;
import java.util.List;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.xml.sax.SAXException;
import applab.search.feeds.ParseIconLocationsFeedXml;
import applab.Location;

;

/**
 * Servlet implementation class UpdateIconLocationsFeed
 */
public class UpdateIconLocationsFeed extends ApplabServlet {

    private static final long serialVersionUID = 1L;

    public final static String REQUEST_ELEMENT_NAME = "locations";
    public final static String RESPONSE_ELEMENT_NAME = "locations";
    public final static String ROW_ELEMENT_NAME = "location";
    public final static String LOCATION_ID_ELEMENT_NAME = "location_id";

    public static List<LocationRequest> locationRequests;

    /**
     * Given a post body in the form: <?xml version="1.0"?> <locations> <location>
     * <subcounty_id>a0pS0000001pW1NIAU</subcounty_id> <longitude>32.28953</longitude> <latitude>2.8378</latitude>
     * </location> <location> <subcounty_id>a0pS0000001pVvEIAU</subcounty_id> <longitude>32.598167</longitude>
     * <latitude>0.3265964</latitude> </location> . . . </locations>
     * 
     * Returns : <?xml version="1.0"?> <locations> <location> <subcounty_id>a0pS0000001pW1NIAU</subcounty_id>
     * <location_id>1093423478</location_id> </location> <location> <subcounty_id>a0pS0000001pVvEIAU</subcounty_id>
     * <location_id>1093423478</location_id> </location> . . . </locations>
     */

    @Override
    protected void doApplabPost(HttpServletRequest request,
                                HttpServletResponse response, ServletRequestContext context)
            throws ServletException, IOException, SAXException, ParserConfigurationException, ParseException, ClassNotFoundException,
            SQLException, ServiceException, TransformerException {

        String iconLocationIdsFeedUrl = request.getSession().getServletContext().getRealPath("/") + "WEB-INF/IconLocationIds.xml";

        locationRequests = new ArrayList<LocationRequest>();

        ParseIconLocationsFeedXml feed = new ParseIconLocationsFeedXml(iconLocationIdsFeedUrl);

        Document requestXml = XmlHelpers.parseXml(request.getReader());
        parseRequest(requestXml);

        response.setStatus(HttpServletResponse.SC_OK);

        context.writeXmlHeader();
        context.writeStartElement(RESPONSE_ELEMENT_NAME);

        for (LocationRequest locationRequest : locationRequests) {
            context.writeStartElement(ROW_ELEMENT_NAME);

            context.writeStartElement(LocationRequestElement.subcounty_id.toString());
            context.print(locationRequest.getSubcountyId());
            context.writeEndElement();

            context.writeStartElement(LOCATION_ID_ELEMENT_NAME);

            // Get closest location in ICON Weather System
            Location location = feed.findClosestLocationId(locationRequest.getLatitude(), locationRequest.getLongitude());
            context.print(location.getLocationId());

            context.writeEndElement();

            context.writeEndElement();
        }
        context.writeEndElement();
        context.println("");
    }

    /***
     * Parses the Location Ids request Xml to create LocationRequest objects If the string is empty, caller can respond
     * with 400: Bad Request
     * 
     * @param requestXml
     *            - Xml Document object representing all Location Requests
     * 
     * @throws ParseException
     */
    private static void parseRequest(Document requestXml) throws ParseException {
        assert (requestXml != null);

        requestXml.normalizeDocument();

        // The <locations/> node
        Element rootNode = requestXml.getDocumentElement();

        // Go through all the <location .../> tags creating Location objects
        for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode
                .getNextSibling()) {
            if (childNode.getNodeType() == Node.ELEMENT_NODE) {
                if (childNode.getLocalName().equals("location")) {
                    parseLocationRequestItem((Element)childNode);
                }
            }
        }
    }

    /**
     * Creates a LocationRequest object from a single <location .../>
     * 
     * @param itemNode
     *            - Xml Element object representing a single <location .../> tag
     * 
     * @throws ParseException
     */
    private static void parseLocationRequestItem(Element itemNode) throws ParseException {

        String subcountyId = null;
        String longitude = null;
        String latitude = null;

        for (Node childNode = itemNode.getFirstChild(); childNode != null; childNode = childNode
                    .getNextSibling()) {
            if (childNode.getNodeType() == Node.ELEMENT_NODE) {

                LocationRequestElement locationRequestElement = LocationRequestElement.valueOf(childNode
                            .getLocalName());

                switch (locationRequestElement) {
                    case subcounty_id:
                        subcountyId = XmlHelpers.parseCharacterData((Element)childNode);
                        break;
                    case longitude:
                        longitude = XmlHelpers.parseCharacterData((Element)childNode);
                        break;
                    case latitude:
                        latitude = XmlHelpers.parseCharacterData((Element)childNode);
                        break;
                }
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
            return subcountyId;
        }

        public String getLongitude() {
            return longitude;
        }

        public String getLatitude() {
            return latitude;
        }
    }

    private enum LocationRequestElement {
          subcounty_id, 
          longitude, 
          latitude;
    }

}
