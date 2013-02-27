/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.server;

import applab.server.DatabaseTable;
import applab.server.SelectCommand;
import java.sql.ResultSet;
import java.sql.SQLException;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

public class KeywordsContentBuilder {
    private static final String REQUEST_ELEMENT_NAME = "GetKeywordsRequest";
    private static final String LOCAL_VERSION_ELEMENT_NAME = "localKeywordsVersion";

    public static ResultSet doSelectQuery(SelectCommand selectCommand, Document requestXml) throws ClassNotFoundException, SQLException {
        String localVersion = getLocalKeywordsVersion(requestXml);
        selectCommand.addField("keyword.id", "keywordId");
        selectCommand.addField("keyword.weight", "keywordWeight");
        selectCommand.addField("keyword.content", "keywordContent");
        selectCommand.addField("keyword.attribution", "keywordAttribution");
        selectCommand.addField("keyword.updated", "keywordUpdated");
        selectCommand.addField("category.name", "categoryName");
        selectCommand.innerJoin(DatabaseTable.Category, "category.id = keyword.categoryId");
        selectCommand.addField("keyword.keyword", "keywordValue");
        selectCommand.addField("keyword.isDeleted", "isDeleted");

        selectCommand.addField("category.ckwsearch", "active");

        if (localVersion.length() > 0) {
            selectCommand.where("(keyword.updated > '" + localVersion + "' or category.updated > '" + localVersion + "')");
        }
        else {
            selectCommand.whereNot("keyword.isDeleted");
        }

        return selectCommand.execute();
    }

    public static String getLocalKeywordsVersion(Document requestXml) {
        String lastClientUpdate = "";
        Element rootElement = requestXml.getDocumentElement();

        if (("http://schemas.applab.org/2010/07/search".equals(rootElement.getNamespaceURI()))
                && ("GetKeywordsRequest".equals(rootElement.getLocalName()))) {
            NodeList nodeList = requestXml.getElementsByTagName("localKeywordsVersion");
            lastClientUpdate = nodeList.item(0).getTextContent();
        }
        return lastClientUpdate;
    }
}