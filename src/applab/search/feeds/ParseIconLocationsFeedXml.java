/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.feeds;

import applab.Location;
import applab.server.XmlHelpers;
import java.io.File;
import java.io.IOException;
import java.io.PrintStream;
import java.text.ParseException;
import java.util.ArrayList;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.transform.TransformerException;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.xml.sax.SAXException;

public class ParseIconLocationsFeedXml {
    private ArrayList<Location> locations;
    private String iconLocationIdsFeedUrl;

    public ParseIconLocationsFeedXml(String iconLocationIdsFeedUrl) {
        this.iconLocationIdsFeedUrl = iconLocationIdsFeedUrl;
        initIconLocationsFeed();
    }

    private void initIconLocationsFeed() {
        this.locations = new ArrayList();
    }

    public ArrayList<Location> parseLocationsXml() throws IOException, SAXException, ParserConfigurationException, ParseException,
            TransformerException {
        String xml = getIconLocationsXml();
        if (xml == null) {
            return null;
        }
        if (cleanUpLocationsXml(xml)) {
            return this.locations;
        }
        return null;
    }

    public Location findClosestLocationId(String latitude, String longitude) throws IOException, SAXException,
            ParserConfigurationException, ParseException, TransformerException {
        try {
            Location location = new Location(Float.valueOf(Float.parseFloat(latitude)), Float.valueOf(Float.parseFloat(longitude)));
            System.out.println(location.getLatitude());
        }
        catch (Exception exc) {
            exc.printStackTrace();
            return null;
        }
        Location location;
        ArrayList allLocations = parseLocationsXml();

        double minimumDistance = 0.0D;
        double currentDistance = 0.0D;
        Location nearestLocation = new Location();

        for (Location currentLocation : allLocations) {
            currentDistance = getDistance(location, currentLocation);
            if (minimumDistance == 0.0D) {
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
        return null;
    }

    private String getIconLocationsXml() throws IOException, SAXException, ParserConfigurationException, TransformerException {
        File file = new File(this.iconLocationIdsFeedUrl);
        Document document = XmlHelpers.parseXml(file);
        return XmlHelpers.exportAsString(document);
    }

    private boolean cleanUpLocationsXml(String xml) throws SAXException, IOException, ParserConfigurationException, ParseException {
        Document xmlDocument = XmlHelpers.parseXml(xml);
        xmlDocument.normalizeDocument();
        Element rootNode = xmlDocument.getDocumentElement();

        parseXmlToLocations(rootNode);
        return true;
    }

    private void parseXmlToLocations(Element rootNode) throws ParseException {
        for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode.getNextSibling()) {
            if ((childNode.getNodeType() != 1) || (!childNode.getLocalName().equals("locations")))
                continue;
            createLocation((Element)childNode);
        }
    }

    private void createLocation(Element itemNode) throws ParseException {
        Location location = new Location();
        for (Node childNode = itemNode.getFirstChild(); childNode != null; childNode = childNode.getNextSibling()) {
            if (childNode.getNodeType() == 1) {
                LocationItemElement itemElement = LocationItemElement.valueOf(childNode.getLocalName());

                switch (itemElement) {
                    case City:
                        location.setLocationId(XmlHelpers.parseCharacterData((Element)childNode));
                        break;
                    case Coastal:
                        location.setLongitude(XmlHelpers.parseCharacterData((Element)childNode));
                        break;
                    case Country:
                        location.setLatitude(XmlHelpers.parseCharacterData((Element)childNode));
                        break;
                }
            }
        }
        this.locations.add(location);
    }

    private double getDistance(Location X, Location Y) {
        double R = 6371.0D;
        double DLat = (Y.getLatitude().floatValue() - X.getLatitude().floatValue()) * 3.141592653589793D / 180.0D;
        double DLon = (Y.getLongitude().floatValue() - X.getLongitude().floatValue()) * 3.141592653589793D / 180.0D;
        double A = Math.sin(DLat / 2.0D) * Math.sin(DLat / 2.0D) + Math.cos(X.getLatitude().floatValue() * 3.141592653589793D / 180.0D)
                * Math.cos(Y.getLatitude().floatValue() * 3.141592653589793D / 180.0D) * Math.sin(DLon / 2.0D) * Math.sin(DLon / 2.0D);
        double c = 2.0D * Math.atan2(Math.sqrt(A), Math.sqrt(1.0D - A));
        double D = R * c;
        return D;
    }

    private static enum LocationItemElement {
        LocationID,
        Longitude,
        Latitude,
        City,
        Country,
        Coastal;
    }
}