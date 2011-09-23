/**
 *
 * Copyright (c) 2011 AppLab, Grameen Foundation
 *
 **/

package applab.search.feeds;

import java.io.File;
import java.io.IOException;
import java.text.ParseException;
import java.util.ArrayList;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.TransformerException;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.xml.sax.SAXException;
import applab.server.XmlHelpers;
import applab.Location;

/**
 * Class to get locations Ids from the ICON Feed Locations Xml.
 * 
 */
public class ParseIconLocationsFeedXml {

    private ArrayList<Location> locations;
    private String iconLocationIdsFeedUrl;

    public ParseIconLocationsFeedXml(String iconLocationIdsFeedUrl) {

        this.iconLocationIdsFeedUrl = iconLocationIdsFeedUrl;
        initIconLocationsFeed();
    }

    /***
     * Initializes locations collection.
     */
    private void initIconLocationsFeed() {

        locations = new ArrayList<Location>();
    }

    /***
     * Gets all locations from the ICON locations feed.
     * 
     * @return - List of Locations representing all available ICON locations
     * 
     * @throws IOException
     * @throws SAXException
     * @throws ParserConfigurationException
     * @throws ParseException
     * @throws TransformerException
     */
    public ArrayList<Location> parseLocationsXml() throws IOException, SAXException,
            ParserConfigurationException, ParseException, TransformerException {

        String xml = getIconLocationsXml();

        if (xml == null) {
            return null;
        }

        if (cleanUpLocationsXml(xml)) {
            return this.locations;
        }

        return null;
    }

    /***
     * Gets the ICON location Id for a given GPS location.
     * 
     * @param latitude
     *            - String representing the latitude of the location
     * 
     * @param longitude
     *            - String representing the longitude of the location
     * 
     * @return - String representing the ICON location Id for the closest location to the given latitude
     * @throws IOException
     * @throws SAXException
     * @throws ParserConfigurationException
     * @throws ParseException
     * @throws TransformerException
     */
    public Location findClosestLocationId(String latitude, String longitude) throws IOException, SAXException,
            ParserConfigurationException,
            ParseException, TransformerException {

        Location location;
        try {
            location = new Location(Float.parseFloat(latitude), Float.parseFloat(longitude));
            System.out.println(location.getLatitude());
        }
        catch (Exception exc) {
            exc.printStackTrace();
            return null;
        }

        // Since this gets called outside the class, it is necessary to reload the collection
        ArrayList<Location> allLocations = parseLocationsXml();

        double minimumDistance = 0;
        double currentDistance = 0;
        Location nearestLocation = new Location();

        for (Location currentLocation : allLocations) {
            // call get Distance
            currentDistance = getDistance(location, currentLocation);
            
         // Initialize, the first time
            if (minimumDistance == 0) {
                minimumDistance = currentDistance; 
            }
            else if (currentDistance < minimumDistance) {
                minimumDistance = currentDistance;
                nearestLocation = currentLocation;
            }
        }
        if (nearestLocation != null) {
            return nearestLocation;
        }
        else {
            return null;
        }
    }

    /**
     * Gets location Ids Xml string from ICON Location Ids Xml File The ICON location Ids XML will be stored on the
     * filesystem TODO : File will be updated every month by a CRON Job
     * 
     * @return - String containing the Location Ids Xml
     * 
     * @throws IOException
     * @throws SAXException
     * @throws ParserConfigurationException
     * @throws TransformerException
     */
    private String getIconLocationsXml() throws IOException, SAXException, ParserConfigurationException, TransformerException {

        File file = new File(iconLocationIdsFeedUrl);
        Document document = XmlHelpers.parseXml(file);
        return XmlHelpers.exportAsString(document);
    }

    /**
     * Normalizes locations xml
     * 
     * @param xml
     *            - The locations xml
     * @return - true if the parsing of locations xml is successful
     * 
     * @throws SAXException
     * @throws IOException
     * @throws ParserConfigurationException
     * @throws ParseException
     */
    private boolean cleanUpLocationsXml(String xml)
            throws SAXException, IOException, ParserConfigurationException,
            ParseException {

        // Normalize the xml
        Document xmlDocument = XmlHelpers.parseXml(xml);
        xmlDocument.normalizeDocument();
        Element rootNode = xmlDocument.getDocumentElement();

        parseXmlToLocations(rootNode);
        return true;
    }

    /**
     * Creates Location objects for all <locations> tags.
     * 
     * @param rootNode
     *            - The root node (<dataroot ... />)
     * 
     * @throws ParseException
     */
    private void parseXmlToLocations(Element rootNode) throws ParseException {
        for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode
                .getNextSibling()) {
            if (childNode.getNodeType() == Node.ELEMENT_NODE) {
                if (childNode.getLocalName().equals("locations")) {
                    createLocation((Element)childNode);
                }
            }
        }
    }

    /**
     * Method to create a Location object from the corresponding <location> tag
     * 
     * @param itemNode
     *            - The current location node
     * 
     * @throws ParseException
     */
    private void createLocation(Element itemNode) throws ParseException {

        Location location = new Location();

        // Populate the location object based on values of the child nodes in the <locations>
        for (Node childNode = itemNode.getFirstChild(); childNode != null; childNode = childNode
                .getNextSibling()) {
            if (childNode.getNodeType() == Node.ELEMENT_NODE) {
                LocationItemElement itemElement = LocationItemElement.valueOf(childNode
                        .getLocalName());

                switch (itemElement) {
                    case LocationID:
                        location.setLocationId(XmlHelpers.parseCharacterData((Element)childNode));
                        break;
                    case Longitude:
                        location.setLongitude(XmlHelpers.parseCharacterData((Element)childNode));
                        break;
                    case Latitude:
                        location.setLatitude(XmlHelpers.parseCharacterData((Element)childNode));
                        break;
                }
            }
        }

        locations.add(location);
    }

    /**
     * Method to compute the distance between any two locations based on Latitude and Longitude Uses Haversine's formula
     * 
     * @param X
     *            - The first location.
     * 
     * @param Y
     *            - The second location.
     * 
     * @return - double representing the distance in kms between the two locations
     */
    private double getDistance(Location X, Location Y) {

        double R = 6371;
        double DLat = (Y.getLatitude() - X.getLatitude()) * Math.PI / 180.0;
        double DLon = (Y.getLongitude() - X.getLongitude()) * Math.PI / 180.0;
        double A = Math.sin(DLat / 2) * Math.sin(DLat / 2) + Math.cos(X.getLatitude() * Math.PI / 180.0)
                * Math.cos(Y.getLatitude() * Math.PI / 180.0) * Math.sin(DLon / 2) * Math.sin(DLon / 2);
        double c = 2 * Math.atan2(Math.sqrt(A), Math.sqrt(1.0 - A));
        double D = R * c;
        return D;
    }

    private enum LocationItemElement {
        LocationID, 
        Longitude, 
        Latitude, 
        City, 
        Country, 
        Coastal;
    }
}
