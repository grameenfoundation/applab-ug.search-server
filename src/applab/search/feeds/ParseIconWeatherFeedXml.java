/**
 *
 * Copyright (c) 2011 AppLab, Grameen Foundation
 *
 **/

package applab.search.feeds;

import applab.net.HttpGet;
import applab.net.HttpResponse;
import applab.server.DatabaseHelpers;
import applab.server.DatabaseTable;
import applab.server.WebAppId;
import applab.server.XmlHelpers;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import javax.xml.parsers.ParserConfigurationException;
import org.w3c.dom.Attr;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NamedNodeMap;
import org.w3c.dom.Node;
import org.xml.sax.SAXException;

public class ParseIconWeatherFeedXml
{
  private static final String attribution = "Information provided by the ICON Weather Feed Service";
  private static final String keywordBase = "Daily_and_5_day_Forecast";
  ArrayList<WeatherConditionsKeyword> keywords;
  private Connection connection;
  private PreparedStatement insertStatement;
  private PreparedStatement updateStatement;
  private String iconWeatherFeedUrl;
  private String iconWeatherForecastFeedUrl;
  private Integer categoryId;

  public ParseIconWeatherFeedXml(Integer categoryId, String iconWeatherFeedUrl, String iconWeatherForecastFeedUrl)
  {
    this.categoryId = categoryId;
    this.iconWeatherFeedUrl = iconWeatherFeedUrl;
    this.iconWeatherForecastFeedUrl = iconWeatherForecastFeedUrl;
    initIconWeatherFeed();
  }

  private void initIconWeatherFeed()
  {
    this.keywords = new ArrayList();
  }

  public boolean parseWeatherRequest(Document requestXml)
    throws ParseException
  {
    assert (requestXml != null);

    requestXml.normalizeDocument();
    Element rootNode = requestXml.getDocumentElement();

    for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode
      .getNextSibling())
    {
      if ((childNode.getNodeType() != 1) || 
        (!childNode.getLocalName().equals("location"))) continue;
      parseWeatherRequestItem((Element)childNode);
    }

    return true;
  }

  private String getIconWeatherConditionsXml(String locationId)
    throws IOException
  {
    HttpGet request = new HttpGet(this.iconWeatherFeedUrl + locationId);
    HttpResponse response = request.getResponse();
    return response.getBodyAsString();
  }

  private String getIconWeatherForecastXml(String locationId)
    throws IOException
  {
    HttpGet request = new HttpGet(this.iconWeatherForecastFeedUrl + locationId);
    HttpResponse response = request.getResponse();
    return response.getBodyAsString();
  }

  private void parseWeatherRequestItem(Element itemNode)
    throws ParseException
  {
    String subcountyName = "";
    String locationId = "";
    String regionName = "";
    String districtName = "";

    for (Node childNode = itemNode.getFirstChild(); childNode != null; childNode = childNode
      .getNextSibling())
    {
      if (childNode.getNodeType() == 1) {
        WeatherRequestElement weatherRequestElement = WeatherRequestElement.valueOf(childNode
          .getLocalName());

        switch (weatherRequestElement) {
        case district_name:
          locationId = XmlHelpers.parseCharacterData((Element)childNode);
          break;
        case location_id:
          subcountyName = XmlHelpers.parseCharacterData((Element)childNode);
          break;
        case region_name:
          regionName = XmlHelpers.parseCharacterData((Element)childNode);
          break;
        case subcounty_name:
          districtName = XmlHelpers.parseCharacterData((Element)childNode);
        }

      }

    }

    this.keywords.add(new WeatherConditionsKeyword("Information provided by the ICON Weather Feed Service", "Daily_and_5_day_Forecast", this.categoryId.intValue(), locationId, subcountyName, districtName, regionName));
  }

  public ArrayList<WeatherConditionsKeyword> parseIconWeather()
    throws IOException, SAXException, ParserConfigurationException, ParseException
  {
    for (WeatherConditionsKeyword keyword : this.keywords) {
      cleanUpWeatherConditionsXml(keyword);
    }

    return this.keywords;
  }

  private void cleanUpWeatherConditionsXml(WeatherConditionsKeyword keyword)
    throws IOException, SAXException, ParserConfigurationException, ParseException
  {
    String xml = getIconWeatherConditionsXml(keyword.getLocationId());

    int cleanXmlStartIndex = xml.indexOf('<');
    xml = xml.substring(cleanXmlStartIndex);

    Document xmlDocument = XmlHelpers.parseXml(xml);
    xmlDocument.normalizeDocument();

    Element rootNode = xmlDocument.getDocumentElement();

    if (parseWeatherConditionsXmlIntoKeyword(rootNode, keyword))
      cleanUpWeatherForecastXml(keyword);
  }

  private boolean parseWeatherConditionsXmlIntoKeyword(Element rootNode, WeatherConditionsKeyword keyword)
    throws ParseException
  {
    Node childNode = rootNode.getFirstChild();

    if ((childNode.getNodeType() == 1) && 
      (childNode.getLocalName().equals("step"))) {
      loadWeatherKeywordInfo((Element)childNode, keyword);
    }

    return true;
  }

  private void loadWeatherKeywordInfo(Element itemNode, WeatherConditionsKeyword keyword)
  {
    NamedNodeMap attributes = itemNode.getAttributes();
    int numberOfAttributes = attributes.getLength();

    for (int index = 0; index < numberOfAttributes; index++) {
      Attr attribute = (Attr)attributes.item(index);

      WeatherItemAttributes weatherItemAttribute = WeatherItemAttributes.valueOf(attribute.getLocalName());

      switch (weatherItemAttribute) {
      case dist:
        keyword.setReadingTime(attribute.getValue());
        break;
      case h:
        keyword.setTemperature(attribute.getValue());
        break;
      case v:
        keyword.setAirpressure(attribute.getValue());
        break;
      case ws:
        keyword.setRelativeHumidity(attribute.getValue());
        break;
      case wn:
        keyword.setVisibility(attribute.getValue());
        break;
      case t:
        keyword.setWindDirection(attribute.getValue());
        break;
      case station:
        keyword.setWindSpeedMax(attribute.getValue());
        break;
      case p:
        keyword.setConditions(parseConditionsToString(attribute.getValue()));
      case dt:
      case rh:
      case s:
      case tf:
      }
    }
  }

  public void cleanUpWeatherForecastXml(WeatherConditionsKeyword keyword)
    throws IOException, SAXException, ParserConfigurationException, ParseException
  {
    String xml = getIconWeatherForecastXml(keyword.getLocationId());

    int cleanXmlStartIndex = xml.indexOf('<');
    xml = xml.substring(cleanXmlStartIndex);

    Document xmlDocument = XmlHelpers.parseXml(xml);
    xmlDocument.normalizeDocument();
    Element rootNode = xmlDocument.getDocumentElement();

    parseXmlToForecasts(rootNode, keyword);
  }

  private void parseXmlToForecasts(Element rootNode, WeatherConditionsKeyword keyword)
  {
    for (Node childNode = rootNode.getFirstChild(); childNode != null; childNode = childNode
      .getNextSibling())
    {
      if ((childNode.getNodeType() != 1) || 
        (!childNode.getLocalName().equals("forecast"))) continue;
      createForecast((Element)childNode, keyword);
    }
  }

  private void createForecast(Element itemNode, WeatherConditionsKeyword keyword)
  {
    try
    {
      Forecast currentForecast = new Forecast();

      NamedNodeMap attributes = itemNode.getAttributes();
      int numberOfAttributes = attributes.getLength();

      for (int index = 0; index < numberOfAttributes; index++) {
        Attr attribute = (Attr)attributes.item(index);
        ForecastItemAttributes weatherForecastItemAttribute = ForecastItemAttributes.valueOf(attribute.getLocalName());

        switch (weatherForecastItemAttribute) {
        case ca:
          currentForecast.setForecastDate(attribute.getValue());
          break;
        case dl:
          currentForecast.setLowTemperature(attribute.getValue());
          break;
        case dt:
          currentForecast.setHighTemperature(attribute.getValue());
          break;
        case pp:
          currentForecast.setConditions(parseConditionsToString(attribute.getValue()));
          break;
        case pr:
          currentForecast.setPrecipitation(attribute.getValue());
          break;
        case set:
          currentForecast.setRainChance(attribute.getValue());
          break;
        case rise:
          currentForecast.setMaximumWindSpeed(attribute.getValue());
          break;
        case s:
          currentForecast.setWindDirection(attribute.getValue());
        }

      }

      keyword.getForecasts().add(currentForecast);
    }
    catch (Exception exc) {
      exc.printStackTrace();
    }
  }

  private String parseConditionsToString(String weatherCondition)
  {
    if (weatherCondition != "")
    {
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

            return "sunny";
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

  public boolean saveToDatabase()
    throws ClassNotFoundException, SQLException
  {
    boolean success = true;
    try
    {
      initDatabaseConnection();
      for (WeatherConditionsKeyword keyword : this.keywords)
        if (keyword.getSubcounty() != null)
        {
          String keywordString = keyword.generateKeyword();
          String contentString = keyword.getContent();

          if ((keywordString != null) && (contentString != null)) {
            if ((updateKeyword(keywordString, contentString, keyword)) || 
              (insertNewKeyword(keyword, keywordString, contentString))) continue;
            success = false;
          }
          else
          {
            success = false;
          }
        }
        else {
          success = false;
        }
    }
    catch (Exception e)
    {
      success = false;
    }
    finally {
      success = cleanOldContent();
      closeDatabaseConnection();
    }

    return success;
  }

  public boolean cleanOldContent() throws SQLException
  {
    boolean success = true;
    Calendar now = Calendar.getInstance();
    now.add(5, -1);
    now.add(3, -2);

    StringBuilder queryText = new StringBuilder();
    queryText.append("UPDATE ");
    queryText.append(DatabaseTable.Keyword.getTableName());
    queryText.append(" SET ");
    queryText.append(" isDeleted = 1,");
    queryText.append(" updated = '");
    queryText.append(DatabaseHelpers.getTimestamp(new java.util.Date()));
    queryText.append("'");
    queryText.append(" WHERE");
    queryText.append(" keyword LIKE '%Daily_and_5_day_Forecast%'");
    queryText.append(" AND updated < '");
    queryText.append(DatabaseHelpers.getTimestamp(now.getTime()));
    queryText.append("'");

    PreparedStatement statement = this.connection.prepareStatement(queryText.toString());
    try
    {
      statement.executeUpdate();
    }
    catch (Exception e)
    {
      success = false;
    }
    finally {
      statement.close();
    }

    return success;
  }

  private void initDatabaseConnection()
    throws ClassNotFoundException, SQLException
  {
    this.connection = DatabaseHelpers.createConnection(WebAppId.search);

    StringBuilder insertText = new StringBuilder();
    insertText.append("INSERT INTO ");
    insertText.append(DatabaseTable.Keyword.getTableName());
    insertText
      .append(" (keyword, categoryId, createDate, content, updated, attribution, otrigger, quizAction_action, quizAction_quizId) ");
    insertText.append("VALUES ");
    insertText.append("(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    this.insertStatement = this.connection.prepareStatement(insertText.toString());

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

  private void closeDatabaseConnection() throws SQLException
  {
    if (this.connection != null) {
      this.connection.close();
    }
    if (this.insertStatement != null) {
      this.insertStatement.close();
    }
    if (this.updateStatement != null)
      this.updateStatement.close();
  }

  private boolean updateKeyword(String keyword, String content, WeatherConditionsKeyword entry)
    throws SQLException, ParseException
  {
    this.updateStatement.clearParameters();
    this.updateStatement.setString(1, content);
    this.updateStatement
      .setTimestamp(2, DatabaseHelpers.getTimestamp(new java.util.Date()));
    this.updateStatement.setInt(3, 0);
    this.updateStatement.setString(4, keyword);

    return this.updateStatement.executeUpdate() != 0;
  }

  private boolean insertNewKeyword(WeatherConditionsKeyword entry, String keyword, String content)
    throws ClassNotFoundException, SQLException, ParseException
  {
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

    return this.insertStatement.executeUpdate() != 0;
  }

  static class Condition
  {
    private int cloudiness;
    private int rainfall;
    private int rainType;
    private boolean night;

    public Condition(int cloudiness, int rainfall, int rainType, boolean night)
    {
      this.cloudiness = cloudiness;
      this.rainfall = rainfall;
      this.rainType = rainType;
      this.night = night;
    }

    public Condition()
    {
    }

    public int getCloudiness() {
      return this.cloudiness;
    }

    public void setCloudiness(int cloudiness) {
      this.cloudiness = cloudiness;
    }

    public int getRainfall() {
      return this.rainfall;
    }

    public void setRainfall(int rainfall) {
      this.rainfall = rainfall;
    }

    public int getRainType() {
      return this.rainType;
    }

    public void setRainType(int rainType) {
      this.rainType = rainType;
    }

    public boolean isNight() {
      return this.night;
    }

    public void setNight(boolean night) {
      this.night = night;
    }
  }

  private static enum ForecastItemAttributes
  {
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
    ca;
  }

  private static enum WeatherItemAttributes
  {
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

  private static enum WeatherRequestElement
  {
    location_id, 
    subcounty_name, 
    region_name, 
    district_name;
  }
}