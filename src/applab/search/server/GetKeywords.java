package applab.search.server;

import java.io.IOException;
import java.sql.SQLException;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;

import org.xml.sax.SAXException;

import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;

/**
 * Server method that returns the keywords requested by the client.
 * 
 * Implements the partial keywords algorithm described in the CKW Search Server functional specification. 
 *
 */
public class GetKeywords extends ApplabServlet {
    private static final long serialVersionUID = 1L;

    // given a post body like:
    // <?xml version="1.0"?>
    // <GetKeywordsRequest xmlns="http://schemas.applab.org/2010/07/seach">
    //   <localKeywordsVersion>Mon, 12 Jul 2010 18:08:33 +0000</localKeywordsVersion>
    // </GetKeywordsRequest>
    // 
    // returns a response like:
    // <?xml version="1.0"?>
    // <GetKeywordsResponse xmlns="http://schemas.applab.org/2010/07/search">
    //  <version>Wed, 21 Jul 2010 11:26:13 +0000</version>
    //  <add id="23219">Farm_Inputs Sironko Sisiyi Seeds</add>
    //  <add id="39243">Animals Bees Pests Wax_moths<add/>
    //  <remove id="45" />
    // </GetKeywordsResponse>
    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context) throws IOException, SAXException, ParserConfigurationException, ClassNotFoundException, SQLException {
        // TODO: add implementation (see applab.surveys.server.Select.java for inspiration) 
    }
}
