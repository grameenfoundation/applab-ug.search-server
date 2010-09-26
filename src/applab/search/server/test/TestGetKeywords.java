package applab.search.server.test;

import junit.framework.Assert;

import org.junit.Test;
import org.w3c.dom.Document;

import applab.search.server.KeywordsContentBuilder;
import applab.server.ApplabConfiguration;
import applab.server.DatabaseTable;
import applab.server.SelectCommand;
import applab.server.SqlImplementation;
import applab.server.XmlHelpers;
import applab.server.test.RemoteSqlImplementation;
import applab.server.test.ServerTestCase;

public class TestGetKeywords extends ServerTestCase {
    private static final String LOCAL_KEYWORDS_VERSION = "2010-07-20 18:34:36";

    private Document mockRequest() {
        StringBuilder requestBuilder = new StringBuilder();
        requestBuilder.append("<?xml version=\"1.0\"?>");
        requestBuilder.append("<GetKeywordsRequest xmlns=\"http://schemas.applab.org/2010/07/search\">");
        requestBuilder.append("<localKeywordsVersion>" + LOCAL_KEYWORDS_VERSION + "</localKeywordsVersion>");
        requestBuilder.append("</GetKeywordsRequest>");

        try {
            return XmlHelpers.parseXml(requestBuilder.toString());
        }
        catch (Exception e) {
            Assert.fail(e.toString());
            return null;
        }
    }

    /**
     * Use to test if the local keywords version is parsed correctly from XML request.
     */
    @Test
    public void testGetLocalKeywordsVersion() {
        String lastClientUpdate = KeywordsContentBuilder.getLocalKeywordsVersion(mockRequest());
        String expected = LOCAL_KEYWORDS_VERSION;
        Assert.assertEquals(expected, lastClientUpdate);
    }

    /**
     * Use to test if a result set is returned for the select query
     * TODO: Comment from Lauren on review (http://pmo.applab.org:8070/cru/OKP-17#c537)
     * Can we do better than checking that the response is non-null? e.g. check that it contains some of the expected nodes or, 
     * ideally, compare it to an expected XML string.
     */
    @Test
    public void testdoSelectQuery() {
        try {
        	Assert.assertNotNull(KeywordsContentBuilder.doSelectQuery(new SelectCommand(DatabaseTable.Keyword), mockRequest()));
        }
        catch (Exception e) {
            Assert.fail(e.toString() + " (Reconfigure build environment?)");
        }

    }

}
