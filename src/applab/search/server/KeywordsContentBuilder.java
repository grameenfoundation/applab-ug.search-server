package applab.search.server;

import java.sql.ResultSet;
import java.sql.SQLException;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;

import applab.server.DatabaseTable;
import applab.server.SelectCommand;

/**
 * Contains methods for parsing the keyword XML request, and querying the database for keyword updates that make up the
 * XML keyword response.
 */
public class KeywordsContentBuilder {
    private final static String REQUEST_ELEMENT_NAME = "GetKeywordsRequest";
    private final static String LOCAL_VERSION_ELEMENT_NAME = "localKeywordsVersion";

    /**
     * Retrieves keywords update from search database
     * 
     * @param selectCommand
     *            object holding SQL select statement
     * @param requestXml
     *            XML DOM request document
     * @return A result set of keyword updates
     * @throws ClassNotFoundException
     * @throws SQLException
     */
    public static ResultSet doSelectQuery(SelectCommand selectCommand, Document requestXml) throws ClassNotFoundException, SQLException {
        String localVersion = KeywordsContentBuilder.getLocalKeywordsVersion(requestXml);
        selectCommand.addField("keyword.id", "keywordId");
        selectCommand.addField("keyword.weight", "keywordWeight");
        selectCommand.addField("keyword.content", "keywordContent");
        selectCommand.addField("keyword.attribution", "keywordAttribution");
        selectCommand.addField("keyword.updated", "keywordUpdated");
        selectCommand.addField("category.name", "categoryName");
        selectCommand.innerJoin(DatabaseTable.Category, "category.id = keyword.categoryId");
        selectCommand.addField("keyword.keyword", "keywordValue");
        selectCommand.addField("keyword.isDeleted", "isDeleted");
        selectCommand.addField("IF(keyword.updated > category.updated, keyword.updated, category.updated)", "version");
        selectCommand.addField("category.ckwsearch", "active");
        
        // retrieve only active content
        // Commented this out, because we also want to pass along items which have since been deactivated
        // selectCommand.whereEquals("category.ckwsearch", "1");
        
        // If we have a version fetch only updated keywords.
        if (localVersion.length() > 0) {
            selectCommand.where("(keyword.updated > '" + localVersion + "' or category.updated > '" + localVersion + "')");
        }
        else {
            selectCommand.whereNot("keyword.isDeleted");
        }
        selectCommand.orderBy("version desc"); // We do this so that the most recently updated one is on top (this allows us to get the next version number)
        return selectCommand.execute();
    }

    /**
     * Parses the request for the local keywords version
     * 
     * @param requestXml
     *            XML DOM document
     * @return A string representing the keywords version date
     */
    public static String getLocalKeywordsVersion(Document requestXml) {
        String lastClientUpdate = "";
        Element rootElement = requestXml.getDocumentElement();

        if (GetKeywords.NAMESPACE.equals(rootElement.getNamespaceURI()) && REQUEST_ELEMENT_NAME.equals(rootElement.getLocalName())) {
            NodeList nodeList = requestXml.getElementsByTagName(LOCAL_VERSION_ELEMENT_NAME);
            lastClientUpdate = nodeList.item(0).getTextContent();
        }
        return lastClientUpdate;
    }
}
