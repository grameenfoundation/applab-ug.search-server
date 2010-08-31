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

import applab.server.*;

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
    private final static String VERSION_ELEMENT_NAME = "version";
    private final static String CATEGORY_ATTRIBUTE_NAME = "category";

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

        DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        Date date = new Date();
        context.writeXmlHeader();
        context.writeStartElement(RESPONSE_ELEMENT_NAME, NAMESPACE);
        context.writeStartElement(VERSION_ELEMENT_NAME);
        context.writeText(dateFormat.format(date));
        context.writeEndElement();
        SelectCommand selectCommand = new SelectCommand(DatabaseTable.Keyword);
        try {
            ResultSet resultSet = KeywordsContentBuilder.doSelectQuery(selectCommand, requestXml);
            HashMap<String, String> attributes = new HashMap<String, String>();
            while (resultSet.next()) {
                attributes.clear();
                if (resultSet.getBoolean("isDeleted")) {
                    attributes.put(ID_ATTRIBUTE_NAME, resultSet.getString("keywordId"));
                    context.writeStartElement(REMOVE_ELEMENT_NAME, attributes);
                    context.writeEndElement();
                }
                else {
                    attributes.put(ID_ATTRIBUTE_NAME, resultSet.getString("keywordId"));
                    attributes.put(CATEGORY_ATTRIBUTE_NAME, resultSet.getString("categoryName"));
                    context.writeStartElement(ADD_ELEMENT_NAME, attributes);
                    context.writeText(resultSet.getString("keywordValue"));
                    context.writeEndElement();
                }
            }
            context.writeEndElement();
        }
        finally {
            if (selectCommand != null) {
                selectCommand.dispose();
            }
        }

    }

}
