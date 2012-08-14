/**
 *
 * Copyright (c) 2011 AppLab, Grameen Foundation
 *
 **/

package applab.search.feeds;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Calendar;
import javax.xml.parsers.ParserConfigurationException;
import org.w3c.dom.Attr;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NamedNodeMap;
import org.w3c.dom.Node;
import org.xml.sax.SAXException;
import applab.net.HttpGet;
import applab.net.HttpResponse;
import applab.server.DatabaseHelpers;
import applab.server.DatabaseTable;
import applab.server.WebAppId;
import applab.server.XmlHelpers;

/**
 * Class gets Weather and Forecast information from the ICON Weather Feed and creates CKW Search Weather Keywords.
 * 
 */
public class ParseIconWeatherFeedXml {

    private static final String attribution = "Information provided by the ICON Weather Feed Service";
    private static final String keywordBase = "Daily_and_5_day_Forecast";

    ArrayList<WeatherConditionsKeyword> keywords; // there is nothing inside here for now

    private Connection connection;
    private PreparedStatement insertStatement;
    private PreparedStatement updateStatement;

    private String iconWeatherFeedUrl;
    private String iconWeatherForecastFeedUrl;
    private Integer categoryId;

    public ParseIconWeatherFeedXml(Integer categoryId, String iconWeatherFeedUrl,
            String iconWeatherForecastFeedUrl) {

        this.categoryId = categoryId;
        this.iconWeatherFeedUrl = iconWeatherFeedUrl;
        this.iconWeatherForecastFeedUrl = iconWeatherForecastFeedUrl;
        initIconWeatherFeed();
    }

    /***
     * Initializes weather keywords collection.
     */
    private void initIconWeatherFeed() {
        keywords = new ArrayList<WeatherConditionsKeyword>();
    }

    /**
     * Parses the Location details from request Xml. 
     * These are the locations for which weather information is to be obtained from the ICON Weather Service.
     * 
     * @param requestXml
     *          - Xml Document object representing all Locations for which weather information will be updated.
     *            
     * @return
     *          - Returns true on success.
     *          
     * @throws ParseException
     */
    public boolean parseWeatherRequest(Document requestXml) throws ParseException {
        assert (requestXml != null);

        requestXml.normalizeDocument();
        Element rootNode = requestXml.getDocumentElement();

        for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode
                .getNextSibling()) {
            if (childNode.getNodeType() == Node.ELEMENT_NODE) {
                if (childNode.getLocalName().equals("location")) {
                    parseWeatherRequestItem((Element)childNode);
                }
            }
        }

        return true;
    }

    /**
     * Calls Icon Weather Feed Service to get weather xml for a given location.
     * 
     * @param locationId
     *          - String for Id of Location in Icon Weather Feed Service.
     * @return
     *          - String representation of xml for today's weather conditions for the given location e.g.
     *          
     * <?xml version="1.0" encoding="utf-8" standalone="yes"?>
     * <weather>
     *  <step 
     *      dt="2011-07-06T11:20:00" 
     *      station="Hamadan" 
     *      t="29" s="d000" 
     *      dist="8" 
     *      tf="29" 
     *      ws="0.0" 
     *      wn="N" 
     *      h="20" 
     *      p="1014" 
     *      v="10000" />
     * </weather>          
     *          
     * @throws IOException
     */
    private String getIconWeatherConditionsXml(String locationId)
            throws IOException {

        HttpGet request = new HttpGet(this.iconWeatherFeedUrl + locationId);
        HttpResponse response = request.getResponse();
        return response.getBodyAsString();

    }
    
    /**
     * Calls Icon Weather Feed Service to get weather forecast xml for a given location.
     * 
     * @param locationId
     *          - String for Id of Location in Icon Weather Feed Service.
     *          
     * @return
     *          - String representation of xml for weather forecast for the given location e.g.
     *          
     * <?xml version="1.0" encoding="utf-8" standalone="yes"?>
     * <weather>
     *  <forecast dt="2011-07-26" tn="30" tx="41" s="d200" pr="0.0" wsx="3.0" wn="SW" />
     *  <forecast dt="2011-07-27" tn="28" tx="39" s="d200" pr="0.0" wsx="3.7" wn="N" />
     *  <forecast dt="2011-07-28" tn="25" tx="36" s="d000" pr="0.0" wsx="3.4" wn="NW" />
     *  <forecast dt="2011-07-29" tn="25" tx="35" s="d000" pr="0.0" wsx="3.3" wn="SW" />
     *  <forecast dt="2011-07-30" tn="27" tx="35" s="d000" pr="0.0" wsx="3.4" wn="SW" />
     *  .
     *  .
     *  .
     * </weather>
     */
    private String getIconWeatherForecastXml(String locationId)
            throws IOException {

        HttpGet request = new HttpGet(this.iconWeatherForecastFeedUrl + locationId);
        HttpResponse response = request.getResponse();
        return response.getBodyAsString();
    }
    
    /**
     * Gets weather request details from weather request element. Also creates base weather keyword with location information.
     * 
     * @param itemNode
     *          - Current location node.
     *          
     * @throws ParseException
     */
    private void parseWeatherRequestItem(Element itemNode) throws ParseException {

        String subcountyName = "";
        String locationId = "";
        String regionName = "";
        String districtName = "";

        for (Node childNode = itemNode.getFirstChild(); childNode != null; childNode = childNode
                    .getNextSibling()) {
            if (childNode.getNodeType() == Node.ELEMENT_NODE) {
                WeatherRequestElement weatherRequestElement = WeatherRequestElement.valueOf(childNode
                            .getLocalName());

                switch (weatherRequestElement) {
                    case location_id:
                        locationId = XmlHelpers.parseCharacterData((Element)childNode);
                        break;
                    case subcounty_name:
                        subcountyName = XmlHelpers.parseCharacterData((Element)childNode);
                        break;
                    case region_name:
                        regionName = XmlHelpers.parseCharacterData((Element)childNode);
                        break;
                    case district_name:
                        districtName = XmlHelpers.parseCharacterData((Element)childNode);
                        break;
                }
            }
        }
        
        //Create weather keyword with base information
        keywords.add(new WeatherConditionsKeyword(attribution, keywordBase, categoryId, locationId, subcountyName, districtName, regionName));
    }
    
    /***
     * Generates weather keywords for locations in the request xml
     * 
     * @return
     *          - ArrayList of weather conditions keywords
     *          
     * @throws IOException
     * @throws SAXException
     * @throws ParserConfigurationException
     * @throws ParseException
     */
    public ArrayList<WeatherConditionsKeyword> parseIconWeather() throws IOException, SAXException, ParserConfigurationException,
            ParseException {

        for (WeatherConditionsKeyword keyword : this.keywords) {
            this.cleanUpWeatherConditionsXml(keyword);
        }

        return this.keywords;
    }
    
    /***
     * Cleans up Current Weather Conditions Xml
     * 
     * @param keyword
     *          - WeatherConditionsKeyword object for current location
     *          
     * @throws IOException
     * @throws SAXException
     * @throws ParserConfigurationException
     * @throws ParseException
     */
    private void cleanUpWeatherConditionsXml(WeatherConditionsKeyword keyword)
            throws IOException, SAXException, ParserConfigurationException,
            ParseException {
        
        String xml = getIconWeatherConditionsXml(keyword.getLocationId());
        
        //Remove weird characters that are returned at the beginning of the xml from the http call.
        int cleanXmlStartIndex = xml.indexOf('<');
        xml = xml.substring(cleanXmlStartIndex);
        
        Document xmlDocument = XmlHelpers.parseXml(xml);
        xmlDocument.normalizeDocument();
        
        Element rootNode = xmlDocument.getDocumentElement();

        //Only parse forecast if the current weather for the location has been parsed successfully
        if (parseWeatherConditionsXmlIntoKeyword(rootNode, keyword)) {
            this.cleanUpWeatherForecastXml(keyword);
        }

    }
    
    /***
     * Parses weather conditions xml to populate weather conditions keyword
     * 
     * @param rootNode
     *          - The the <weather../> tag
     *          
     * @param keyword
     *          - Weather Conditions Keyword object
     *          
     * @return
     *          - True on success 
     *          
     * @throws ParseException
     */
    private boolean parseWeatherConditionsXmlIntoKeyword(Element rootNode, WeatherConditionsKeyword keyword) throws ParseException {
       
        Node childNode = rootNode.getFirstChild();
        
        if (childNode.getNodeType() == Node.ELEMENT_NODE) {
            if (childNode.getLocalName().equals("step")) {
                loadWeatherKeywordInfo((Element)childNode, keyword);
            }
        }

       return true;
    }

    /***
     * Loads Weather info into weather keyword fields
     * 
     * @param itemNode
     *          - The weather node //<step../> tag
     *           
     * @param keyword
     *          - WeatherConditionsKeyword object for current weather keyword.
     */
    private void loadWeatherKeywordInfo(Element itemNode, WeatherConditionsKeyword keyword) {

        NamedNodeMap attributes = itemNode.getAttributes();
        int numberOfAttributes = attributes.getLength();

        for (int index = 0; index < numberOfAttributes; index++) {
            Attr attribute = (Attr)attributes.item(index);

            WeatherItemAttributes weatherItemAttribute = WeatherItemAttributes.valueOf(attribute.getLocalName());

            switch (weatherItemAttribute) {
                case dt:
                    keyword.setReadingTime(attribute.getValue());
                    break;
                case t:
                    keyword.setTemperature(attribute.getValue());
                    break;
                case p:
                    keyword.setAirpressure(attribute.getValue());
                    break;
                case rh:
                    keyword.setRelativeHumidity(attribute.getValue());
                    break;
                case v:
                    keyword.setVisibility(attribute.getValue());
                    break;
                case wn:
                    keyword.setWindDirection(attribute.getValue());
                    break;
                case ws:
                    keyword.setWindSpeedMax(attribute.getValue());
                    break;
                case s:
                    keyword.setConditions(parseConditionsToString(attribute.getValue()));
                    break;
            }
        }

    }
 
    /**
     * Cleans up Weather Forecast Xml
     * 
     * @param keyword
     *          - WeatherConditionsKeyword object for current location
     *          
     * @throws IOException
     * @throws SAXException
     * @throws ParserConfigurationException
     * @throws ParseException
     */
    public void cleanUpWeatherForecastXml(WeatherConditionsKeyword keyword)
            throws IOException, SAXException, ParserConfigurationException,
            ParseException {

        String xml = getIconWeatherForecastXml(keyword.getLocationId());

        //Remove weird characters that are returned at the beginning of the xml from the http call.
        int cleanXmlStartIndex = xml.indexOf('<');
        xml = xml.substring(cleanXmlStartIndex);
        
        Document xmlDocument = XmlHelpers.parseXml(xml);
        xmlDocument.normalizeDocument();
        Element rootNode = xmlDocument.getDocumentElement();
        
        parseXmlToForecasts(rootNode, keyword);

    }

    /**
     * Creates Forecast objects for weather keyword.
     * 
     * @param rootNode
     *            - The root node (<weather ... />)
     * 
     */
    private void parseXmlToForecasts(Element rootNode, WeatherConditionsKeyword keyword) {
        for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode
                .getNextSibling()) {

            if (childNode.getNodeType() == Node.ELEMENT_NODE) {
                if (childNode.getLocalName().equals("forecast")) {
                    createForecast((Element)childNode, keyword);

                }
            }
        }
    }
    
    /**
     * Creates a Forecast object from a single <forecast .../> tag.
     * 
     * @param itemNode
     *            - Xml Element object representing a single <location .../> tag
     *            
     * @param keyword
     *            - WeatherConditionsKeyword object for current keyword
     *            
     * @throws ParseException
     */
    private void createForecast(Element itemNode, WeatherConditionsKeyword keyword) {
        try {
            Forecast currentForecast = new Forecast();

            NamedNodeMap attributes = itemNode.getAttributes();
            int numberOfAttributes = attributes.getLength();

            for (int index = 0; index < numberOfAttributes; index++) {
                Attr attribute = (Attr)attributes.item(index);
                ForecastItemAttributes weatherForecastItemAttribute = ForecastItemAttributes.valueOf(attribute.getLocalName());

                switch (weatherForecastItemAttribute) {
                    case dt:
                        currentForecast.setForecastDate(attribute.getValue());
                        break;
                    case tn:
                        currentForecast.setLowTemperature(attribute.getValue());
                        break;
                    case tx:
                        currentForecast.setHighTemperature(attribute.getValue());
                        break;
                    case s:
                        currentForecast.setConditions(parseConditionsToString(attribute.getValue()));
                        break;
                    case pr:
                        currentForecast.setPrecipitation(attribute.getValue());
                        break;
                    case pp:
                        currentForecast.setRainChance(attribute.getValue());
                        break;
                    case wsx:
                        currentForecast.setMaximumWindSpeed(attribute.getValue());
                        break;
                    case wn:
                        currentForecast.setWindDirection(attribute.getValue());
                        break;
                    default:
                        break;
                }
            }
            
            //add to list of forecasts for current weather keyword
            keyword.getForecasts().add(currentForecast);
        }
        catch (Exception exc) {
            exc.printStackTrace();
        }
    }

    /**
     * Helper method to convert coded weather conditions string to human readable form.
     * 
     * @param weatherCondition
     *          - String for encoded weather conditions text e.g.
     *            weatherCondition = d000
     * @return
     *          - String representing human readable form of weather conditions
     *          
     */
    private String parseConditionsToString(String weatherCondition) {
        if (weatherCondition != "") {

            Condition condition = new Condition();
            condition.setNight(!weatherCondition.substring(0, 1).equals("d"));
            condition.setCloudiness(Integer.parseInt(weatherCondition.substring(1, 2)));
            condition.setRainfall(Integer.parseInt(weatherCondition.substring(2, 3)));
            condition.setRainType(Integer.parseInt(weatherCondition.substring(3)));

            switch (condition.getRainType()) {
                case 0:
                    switch (condition.getRainfall()) {
                        case 0:
                            switch (condition.getCloudiness()) {
                                case 0:
                                case 1:
                                    if (condition.isNight()) {
                                        return "clear";
                                    }
                                    else {
                                        return "sunny";
                                    }
                                case 2:
                                case 3:
                                case 4:
                                    return "cloudy";

                            }
                        case 1:
                        case 2:
                        case 3:
                            return "rainy";
                        case 4:
                            return "stormy";
                    }
                case 1:
                    return "sleet";
                case 2:
                    return "snow";

            }
        }

        return null;
    }
    
    /**
     * Saves all weather conditions keywords to database and salesforce
     * 
     * @return
     *          - boolean representing whether keywords were saved to database or not.
     *          
     * @throws ClassNotFoundException
     * @throws SQLException
     */
    public boolean save() throws ClassNotFoundException, SQLException {
        return saveToDatabase() & saveToSalesforce();
    }

    /**
     * 
     * @return
     *          - boolean representing whether keywords were saved to salesforce or not.
     */
    private boolean saveToSalesforce() {
        boolean success = true;

        try {
           
        }
        catch (Exception e) {
            success = false;
        }
        finally {
            
        }

        return success;
    }

    /**
     * Saves all weather conditions keywords to database
     * 
     * @return
     *          - boolean representing whether keywords were saved or not.
     *          
     * @throws ClassNotFoundException
     * @throws SQLException
     */
    private boolean saveToDatabase()
            throws ClassNotFoundException, SQLException {

        boolean success = true;

        try {
            initDatabaseConnection();
            for (WeatherConditionsKeyword keyword : this.keywords) {
                if (keyword.getSubcounty() != null) {

                    String keywordString = keyword.generateKeyword();
                    String contentString = keyword.getContent();

                    if (keywordString != null && contentString != null) {
                        if (!updateKeyword(keywordString, contentString, keyword)) {
                            if (!insertNewKeyword(keyword, keywordString, contentString)) {
                                success = false;
                            }
                        }
                    }
                    else {
                        success = false;
                    }
                }
                else {
                    success = false;
                }
            }
        }
        catch (Exception e) {
            success = false;
        }
        finally {
            success = cleanOldContent();
            closeDatabaseConnection();
        }

        return success;
    }

    public boolean cleanOldContent() throws SQLException {

        boolean success = true;
        Calendar now = Calendar.getInstance();
        now.add(Calendar.DATE, -1);
        now.add(Calendar.WEEK_OF_YEAR, -2);

        StringBuilder queryText = new StringBuilder();
        queryText.append("UPDATE ");
        queryText.append(DatabaseTable.Keyword.getTableName());
        queryText.append(" SET ");
        queryText.append(" isDeleted = 1,");
        queryText.append(" updated = '");
        queryText.append(DatabaseHelpers.getTimestamp(new java.util.Date()));
        queryText.append("'");
        queryText.append(" WHERE");
        queryText.append(" keyword LIKE '%" + keywordBase + "%'");
        queryText.append(" AND updated < '");
        queryText.append(DatabaseHelpers.getTimestamp(now.getTime()));
        queryText.append("'");

        PreparedStatement statement = this.connection.prepareStatement(queryText.toString());

        try {
            statement.executeUpdate();
        }
        catch (Exception e) {
            // Do nothing as the finally that calls this will close the connections
            success = false;
        }
        finally {
            statement.close();
        }

        return success;
    }

    private void initDatabaseConnection()
            throws ClassNotFoundException, SQLException {

        this.connection = DatabaseHelpers.createConnection(WebAppId.search);

        // Prepare the insert statement
        StringBuilder insertText = new StringBuilder();
        insertText.append("INSERT INTO ");
        insertText.append(DatabaseTable.Keyword.getTableName());
        insertText
                .append(" (keyword, categoryId, createDate, content, updated, attribution, otrigger, quizAction_action, quizAction_quizId) ");
        insertText.append("VALUES ");
        insertText.append("(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        this.insertStatement = this.connection.prepareStatement(insertText.toString());

        // Prepare the update statement
        StringBuilder updateText = new StringBuilder();
        updateText.append("UPDATE ");
        updateText.append(DatabaseTable.Keyword.getTableName());
        updateText.append(" SET ");
        updateText.append("content = ?, ");
        updateText.append("updated = ?, ");
        updateText.append("isDeleted = ? ");
        updateText.append("WHERE ");
        updateText.append("keyword = ? ");
        this.updateStatement = this.connection.prepareStatement(updateText.toString());
    }

    private void closeDatabaseConnection()
            throws SQLException {
        if (this.connection != null) {
            this.connection.close();
        }
        if (this.insertStatement != null) {
            this.insertStatement.close();
        }
        if (this.updateStatement != null) {
            this.updateStatement.close();
        }
    }

    private boolean updateKeyword(String keyword, String content, WeatherConditionsKeyword entry)
            throws SQLException, ParseException {

        this.updateStatement.clearParameters();
        this.updateStatement.setString(1, content);
        this.updateStatement
                .setTimestamp(2, DatabaseHelpers.getTimestamp(new java.util.Date()));
        this.updateStatement.setInt(3, 0);
        this.updateStatement.setString(4, keyword);

        if (this.updateStatement.executeUpdate() == 0) {
            return false;
        }

        return true;
    }

    private boolean insertNewKeyword(WeatherConditionsKeyword entry, String keyword, String content)
            throws ClassNotFoundException, SQLException, ParseException {

        this.insertStatement.clearParameters();

        this.insertStatement.setString(1, keyword);
        this.insertStatement.setInt(2, entry.getCategoryId());
        this.insertStatement.setDate(3, new java.sql.Date(new java.util.Date().getTime()));
        this.insertStatement.setString(4, content);
        this.insertStatement
                .setTimestamp(5, DatabaseHelpers.getTimestamp(new java.util.Date()));
        this.insertStatement.setString(6, entry.getAttribution());
        this.insertStatement.setInt(7, 0);
        this.insertStatement.setString(8, "");
        this.insertStatement.setInt(9, 0);

        if (this.insertStatement.executeUpdate() == 0) {
            return false;
        }
        return true;
    }

    //<step dt="2011-09-13T09:00:00" station="Mbarara" t="18" s="d000" dist="128" tf="18" ws="3.1" wn="N" rh="88" p="" v="10000" />
    private enum WeatherItemAttributes {
        dt,
        station,
        t,
        s,
        dist,
        tf,
        ws,
        wn,
        h,
        p,
        v,
        rh;
    }
//<forecast dt="2011-09-27" tn="18" tx="26" s="d210" pr="1.7" wsx="1.8" wn="SE" pp="95" tp="51" rise="06:41" set="18:47" dl="726" uv="" ca="72" />
    private enum ForecastItemAttributes {
        dt,
        tn,
        tx,
        s,
        pr,
        wsx,
        wn,
        pp,
        tp,
        rise,
        set,
        dl,
        uv,
        ca
        ;
    }

    private enum WeatherRequestElement {
        location_id,
        subcounty_name,
        region_name,
        district_name
    }
    
    static class Condition {

        private int cloudiness;
        private int rainfall;
        private int rainType;
        private boolean night;

        public Condition(int cloudiness, int rainfall, int rainType, boolean night) {
            this.cloudiness = cloudiness;
            this.rainfall = rainfall;
            this.rainType = rainType;
            this.night = night;
        }

        public Condition() {

        }

        public int getCloudiness() {
            return cloudiness;
        }

        public void setCloudiness(int cloudiness) {
            this.cloudiness = cloudiness;
        }

        public int getRainfall() {
            return rainfall;
        }

        public void setRainfall(int rainfall) {
            this.rainfall = rainfall;
        }

        public int getRainType() {
            return rainType;
        }

        public void setRainType(int rainType) {
            this.rainType = rainType;
        }

        public boolean isNight() {
            return night;
        }

        public void setNight(boolean night) {
            this.night = night;
        }

    }
}
