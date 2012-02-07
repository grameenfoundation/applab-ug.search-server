package applab.search.server;

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

import applab.server.ApplabServlet;
import applab.server.DatabaseTable;
import applab.server.SelectCommand;
import applab.server.ServletRequestContext;
import applab.server.XmlHelpers;

/**
 * Server method that returns the keywords requested by the client.
 * 
 * Implements the partial keywords algorithm described in the CKW Search Server functional specification.
 * 
 */
public class GetKeywords extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    public final static String NAMESPACE = "http://schemas.applab.org/2010/07/search";
    private final static String RESPONSE_ELEMENT_NAME = "GetKeywordsResponse";
    private final static String ADD_ELEMENT_NAME = "add";
    private final static String REMOVE_ELEMENT_NAME = "remove";
    private final static String ID_ATTRIBUTE_NAME = "id";
    private final static String WEIGHT_ATTRIBUTE_NAME = "order";
    private final static String KEYWORD_ATTRIBUTE_NAME = "keyword";
    private final static String CATEGORY_ATTRIBUTE_NAME = "category";
    private final static String ATTRIBUTION_ATTRIBUTE_NAME = "attribution";
    private final static String UPDATED_ATTRIBUTE_NAME = "updated";
    private static final String VERSION_ATTRIBUTE_NAME = "version";
    private static final String TOTAL_ATTRIBUTE_NAME = "total";

    // Given a post body like:
    // <?xml version="1.0"?>
    // <GetKeywordsRequest xmlns="http://schemas.applab.org/2010/07/search">
    // <localKeywordsVersion>2010-07-13 18:08:33</localKeywordsVersion>
    // </GetKeywordsRequest>
    //
    // returns a response like:
    // <?xml version="1.0"?>
    // <GetKeywordsResponse xmlns="http://schemas.applab.org/2010/07/search"
    // version="2010-07-20 18:34:36" total="25">
    // <add id="23219" category="Farm_Inputs">Sironko Sisiyi Seeds</add>
    // <add id="39243" category="Animals">Bees Pests Wax_moths<add/>
    // <remove id="45" />
    // </GetKeywordsResponse>

    @Override
    protected void doApplabPost(HttpServletRequest request,
                                HttpServletResponse response, ServletRequestContext context)
            throws IOException, SAXException, ParserConfigurationException,
            ClassNotFoundException, SQLException {
        try {
            Document requestXml = context.getRequestBodyAsXml();
            GetKeywords.writeResponse(requestXml, context);
        }
        finally {
            context.close();
        }
    }

    /**
     * Creates SQL query and generates XML response
     * 
     * @param requestXml
     *            XML DOM
     * @param context
     *            the servlet request context
     * @throws SQLException
     * @throws ClassNotFoundException
     * @throws IOException
     */
    public static void writeResponse(Document requestXml,
                                     ServletRequestContext context) throws SQLException,
            ClassNotFoundException, IOException {
        context.writeXmlHeader();

        SelectCommand selectCommand = new SelectCommand(DatabaseTable.Keyword);
        Boolean isFirst = true;

        // Just use the time that it is now for the version controlling.
        DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        Date date = new Date();
        String version = dateFormat.format(date);
        try {
            ResultSet resultSet = KeywordsContentBuilder.doSelectQuery(
                    selectCommand, requestXml);

            // Save the totalSize (we get it here before we iterate)
            Integer total = getResultSetSize(resultSet);

            if (total > 0) {
                HashMap<String, String> attributes = new HashMap<String, String>();
                while (resultSet.next()) {
                    attributes.clear();

                    if (isFirst) {
                        HashMap<String, String> startAttributes = new HashMap<String, String>();
                        startAttributes.put(VERSION_ATTRIBUTE_NAME, version);
                        startAttributes.put(TOTAL_ATTRIBUTE_NAME,
                                total.toString());
                        context.writeStartElement(RESPONSE_ELEMENT_NAME,
                                NAMESPACE, "", startAttributes);

                        isFirst = false;
                    }

                    if (resultSet.getBoolean("isDeleted")
                            || (resultSet.getInt("active") == 0)) {
                        attributes.put(ID_ATTRIBUTE_NAME,
                                resultSet.getString("keywordId"));
                        context.writeStartElement(REMOVE_ELEMENT_NAME,
                                attributes);
                        context.writeEndElement();
                    }
                    else {
                        attributes.put(ID_ATTRIBUTE_NAME,
                                resultSet.getString("keywordId"));

                        // Replace XML escape characters
                        String keywordValue = replaceEscapeCharacters(resultSet
                                .getString("keywordValue"));
                        String attribution = replaceEscapeCharacters(resultSet
                                .getString("keywordAttribution"));
                        String category = replaceEscapeCharacters(resultSet
                                .getString("categoryName"));
                        attributes.put(KEYWORD_ATTRIBUTE_NAME, keywordValue);
                        attributes.put(WEIGHT_ATTRIBUTE_NAME,
                                resultSet.getString("keywordWeight"));
                        attributes.put(CATEGORY_ATTRIBUTE_NAME, category);

                        if (attribution != null
                                && attribution.trim().length() > 0) {
                            attribution = attribution.trim().replace("\r\n",
                                    "\n");
                        }
                        else {
                            attribution = "";
                        }
                        attributes.put(ATTRIBUTION_ATTRIBUTE_NAME, attribution);

                        String updated = resultSet.getString("keywordUpdated");
                        if (updated != null && updated.trim().length() > 0) {
                            updated = XmlHelpers.escapeText(updated.trim()
                                    .replace("\r\n", "\n"));
                        }
                        else {
                            updated = "";
                        }
                        attributes.put(UPDATED_ATTRIBUTE_NAME, updated);

                        context.writeStartElement(ADD_ELEMENT_NAME, attributes);

                        // Content
                        String content = resultSet.getString("keywordContent");
                        if (content != null && content.trim().length() > 0) {
                            content = content.trim().replace("\r\n", "\n");
                            context.writeText(content);
                        }
                        context.writeEndElement();
                    }
                }
                context.writeEndElement(); // Close the first element
            }
            else {
                HashMap<String, String> startAttributes = new HashMap<String, String>();
                startAttributes.put(VERSION_ATTRIBUTE_NAME, version);
                startAttributes.put(TOTAL_ATTRIBUTE_NAME, total.toString());
                context.writeStartElement(RESPONSE_ELEMENT_NAME, NAMESPACE, "",
                        startAttributes);
                context.writeEndElement(); // Close the first element
            }
        }
        finally {
            if (selectCommand != null) {
                selectCommand.dispose();
            }
        }

    }

    // Not the most effecient way of getting total number of rows
    public static int getResultSetSize(ResultSet resultSet) {
        int size = -1;
        int currentRow;

        try {
            currentRow = resultSet.getRow();
            resultSet.last();
            size = resultSet.getRow();

            if (currentRow > 0) {
                resultSet.absolute(currentRow);
            }
            else {
                resultSet.beforeFirst();
            }
        }
        catch (SQLException e) {
            return size;
        }

        return size;
    }

    /**
     * Replaces escape characters in keyword in conformance with XML These include: " - &quot; ' - &apos; < - &lt; > -
     * &gt; & - &amp;
     * 
     * @param keyword
     * @return edited keyword
     */
    private static String replaceEscapeCharacters(String keyword) {
        keyword = keyword.replace("\"", "&quot;");
        keyword = keyword.replace("\'", "&quot;");
        keyword = keyword.replace("<", " &lt;");
        keyword = keyword.replace(">", "&gt;");
        keyword = keyword.replace("&", "&amp;");
        return keyword;
    }
}
