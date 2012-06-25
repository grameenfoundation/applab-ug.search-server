package applab.search.server;

import applab.server.ApplabServlet;
import applab.server.DatabaseTable;
import applab.server.SelectCommand;
import applab.server.ServletRequestContext;
import applab.server.XmlHelpers;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import org.w3c.dom.Document;
import org.xml.sax.SAXException;

public class GetKeywords extends ApplabServlet
{
  private static final long serialVersionUID = 1L;
  public static final String NAMESPACE = "http://schemas.applab.org/2010/07/search";
  private static final String RESPONSE_ELEMENT_NAME = "GetKeywordsResponse";
  private static final String ADD_ELEMENT_NAME = "add";
  private static final String REMOVE_ELEMENT_NAME = "remove";
  private static final String ID_ATTRIBUTE_NAME = "id";
  private static final String WEIGHT_ATTRIBUTE_NAME = "order";
  private static final String KEYWORD_ATTRIBUTE_NAME = "keyword";
  private static final String CATEGORY_ATTRIBUTE_NAME = "category";
  private static final String ATTRIBUTION_ATTRIBUTE_NAME = "attribution";
  private static final String UPDATED_ATTRIBUTE_NAME = "updated";
  private static final String VERSION_ATTRIBUTE_NAME = "version";
  private static final String TOTAL_ATTRIBUTE_NAME = "total";
  private static final String IMEI = "x-Imei";

  protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws IOException, SAXException, ParserConfigurationException, ClassNotFoundException, SQLException
  {
    try
    {
       log("Reached getKeywords servlet");
        
       String imei = request.getHeader(IMEI);
       log("x-Imei: " + imei);
        
      Document requestXml = context.getRequestBodyAsXml();
      String localVersion = KeywordsContentBuilder.getLocalKeywordsVersion(requestXml);
      log("Last update Date: " + localVersion);
      writeResponse(requestXml, context);
    }
    finally {
      context.close();
    }
  }

  public static void writeResponse(Document requestXml, ServletRequestContext context)
    throws SQLException, ClassNotFoundException, IOException
  {
    context.writeXmlHeader();

    SelectCommand selectCommand = new SelectCommand(DatabaseTable.Keyword);
    Boolean isFirst = Boolean.valueOf(true);

    DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
    Date date = new Date();
    String version = dateFormat.format(date);
    try {
      ResultSet resultSet = KeywordsContentBuilder.doSelectQuery(selectCommand, requestXml);

      Integer total = Integer.valueOf(getResultSetSize(resultSet));      
      if (total.intValue() > 0) {
        HashMap attributes = new HashMap();
        while (resultSet.next()) {
          attributes.clear();

          if (isFirst.booleanValue())
          {
            HashMap startAttributes = new HashMap();
            startAttributes.put("version", version);
            startAttributes.put("total", total.toString());
            context.writeStartElement("GetKeywordsResponse", "http://schemas.applab.org/2010/07/search", "", startAttributes);

            isFirst = Boolean.valueOf(false);
          }

          if ((resultSet.getBoolean("isDeleted")) || (resultSet.getInt("active") == 0)) {
            attributes.put("id", resultSet.getString("keywordId"));
            context.writeStartElement("remove", attributes);
            context.writeEndElement();
          }
          else {
            attributes.put("id", resultSet.getString("keywordId"));
            attributes.put("keyword", resultSet.getString("keywordValue"));
            attributes.put("order", resultSet.getString("keywordWeight"));
            attributes.put("category", resultSet.getString("categoryName"));

            String attribution = resultSet.getString("keywordAttribution");
            if ((attribution != null) && (attribution.trim().length() > 0)) {
              attribution = attribution.trim().replace("\r\n", "\n");
              attribution = replaceXmlEscapeCharacters(attribution);
            }
            else {
              attribution = "";
            }
            attributes.put("attribution", attribution);

            String updated = resultSet.getString("keywordUpdated");
            if ((updated != null) && (updated.trim().length() > 0)) {
              updated = XmlHelpers.escapeText(updated.trim().replace("\r\n", "\n"));
              updated = replaceXmlEscapeCharacters(updated);
            }
            else {
              updated = "";
            }
            attributes.put("updated", updated);

            context.writeStartElement("add", attributes);

            String content = resultSet.getString("keywordContent");
            if ((content != null) && (content.trim().length() > 0)) {
              content = content.trim().replace("\r\n", "\n");
              content = replaceXmlEscapeCharacters(content);
              context.writeText(content);
            }
            context.writeEndElement();
          }
        }
        context.writeEndElement();
      }
      else {
        HashMap startAttributes = new HashMap();
        startAttributes.put("version", version);
        startAttributes.put("total", total.toString());
        context.writeStartElement("GetKeywordsResponse", "http://schemas.applab.org/2010/07/search", "", startAttributes);
        context.writeEndElement();
      }
    }
    finally {
      if (selectCommand != null)
        selectCommand.dispose();
    }
  }

  public static int getResultSetSize(ResultSet resultSet)
  {
    int size = -1;
    try
    {
      int currentRow = resultSet.getRow();
      resultSet.last();
      size = resultSet.getRow();

      if (currentRow > 0) {
        resultSet.absolute(currentRow);
      }
      else
        resultSet.beforeFirst();
    }
    catch (SQLException e)
    {
      return size;
    }
    int currentRow;
    return size;
  }
  
  private static String replaceXmlEscapeCharacters(String keyword) {
  
      keyword = keyword.replace("\"", "&quot;");
      keyword = keyword.replace("\'", "&quot;");
      keyword = keyword.replace("<", "&lt");
      keyword = keyword.replace(">", "&gt;");
      keyword = keyword.replace("&", "&amp;");
      return keyword;
      
  }
}