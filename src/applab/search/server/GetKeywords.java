package applab.search.server;

import java.io.IOException;
import java.io.StringReader;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;

import org.w3c.dom.Document;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;

import com.sun.org.apache.xml.internal.resolver.helpers.Debug;

import applab.server.*;
import applab.server.test.RemoteSqlImplementation;

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
    private final static String VERSION_ELEMENT_NAME = "version";
    private final static String CATEGORY_ATTRIBUTE_NAME = "category";
    private final static String ATTRIBUTION_ATTRIBUTE_NAME = "attribution";
    private final static String UPDATED_ATTRIBUTE_NAME = "updated";

    // Given a post body like: <?xml version="1.0"?> <GetKeywordsRequest
    // xmlns="http://schemas.applab.org/2010/07/search">
    // <localKeywordsVersion>2010-07-13 18:08:33</localKeywordsVersion>
    // </GetKeywordsRequest>
    //
    // returns a response like: <?xml version="1.0"?> <GetKeywordsResponse
    // xmlns="http://schemas.applab.org/2010/07/search"> <version>2010-07-20
    // 18:34:36</version> <add id="23219" category="Farm_Inputs">Sironko Sisiyi
    // Seeds</add> <add id="39243" category="Animals">Bees Pests Wax_moths<add/>
    // <remove id="45" /> </GetKeywordsResponse>

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws IOException, SAXException, ParserConfigurationException, ClassNotFoundException, SQLException {
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
    public static void writeResponse(Document requestXml, ServletRequestContext context) throws SQLException, ClassNotFoundException,
            IOException {
        context.writeXmlHeader();
        context.writeStartElement(RESPONSE_ELEMENT_NAME, NAMESPACE);

        SelectCommand selectCommand = new SelectCommand(DatabaseTable.Keyword);
        Boolean isFirst = true;
        try {
            ResultSet resultSet = KeywordsContentBuilder.doSelectQuery(selectCommand, requestXml);
            HashMap<String, String> attributes = new HashMap<String, String>();
            while (resultSet.next()) {
                attributes.clear();

                if (isFirst) {
                    // This is the first result, so we use it's updated time as the version
                    // For this to work, results should be ordered by updated date field descending
                    context.writeStartElement(VERSION_ELEMENT_NAME);
                    String updated = resultSet.getString("keywordUpdated");
                    if (updated != null && updated.trim().length() > 0) {
                        context.writeText(updated);
                    }
                    else {
                        DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
                        Date date = new Date();
                        context.writeText(dateFormat.format(date));
                    }
                    context.writeEndElement();
                    isFirst = false;
                }

                if (resultSet.getBoolean("isDeleted")) {
                    attributes.put(ID_ATTRIBUTE_NAME, resultSet.getString("keywordId"));
                    context.writeStartElement(REMOVE_ELEMENT_NAME, attributes);
                    context.writeEndElement();
                }
                else {
                    attributes.put(ID_ATTRIBUTE_NAME, resultSet.getString("keywordId"));
                    attributes.put(KEYWORD_ATTRIBUTE_NAME, resultSet.getString("keywordValue"));
                    attributes.put(WEIGHT_ATTRIBUTE_NAME, resultSet.getString("keywordWeight"));
                    attributes.put(CATEGORY_ATTRIBUTE_NAME, resultSet.getString("categoryName"));
                    
                    String attribution = resultSet.getString("keywordAttribution");
                    if (attribution != null && attribution.trim().length() > 0) {
                        attribution = XmlHelpers.escapeText(attribution.trim().replace("\r\n", "\n"));
                    }
                    else {
                        attribution = "";
                    }
                    attributes.put(ATTRIBUTION_ATTRIBUTE_NAME, attribution);
                    
                    String updated = resultSet.getString("keywordUpdated");
                    if (updated != null && updated.trim().length() > 0) {
                        updated = XmlHelpers.escapeText(updated.trim().replace("\r\n", "\n"));
                    }
                    else {
                        updated = "";
                    }                        
                    attributes.put(UPDATED_ATTRIBUTE_NAME, updated);
                    
                    context.writeStartElement(ADD_ELEMENT_NAME, attributes);

                    // Content
                    String content = resultSet.getString("keywordContent");
                    if (content != null && content.trim().length() > 0) {
                        content = XmlHelpers.escapeText(content.trim().replace("\r\n", "\n"));
                        context.writeText(content);
                    }
                    context.writeEndElement();
                }
            }
        }
        finally {
            context.writeEndElement();
            if (selectCommand != null) {
                selectCommand.dispose();
            }
        }

    }

}
