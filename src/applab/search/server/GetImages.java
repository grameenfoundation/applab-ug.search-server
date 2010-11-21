package applab.search.server;

import java.io.File;
import java.io.FilenameFilter;
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
public class GetImages extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    public final static String NAMESPACE = "http://schemas.applab.org/2010/07/search";
    private final static String RESPONSE_ELEMENT_NAME = "GetImagesResponse";
    private final static String NAME_ATTRIBUTE_NAME = "name";
    private final static String URL_ATTRIBUTE_NAME = "src";
    private final static String HASH_ATTRIBUTE_NAME = "sha1hash";
    private final static String IMAGE_ELEMENT_NAME = "image";
    public static String imageFilePath = null; // Allows us to change the file path (for testing)

    /*
     * Given a post body like: <?xml version="1.0"?> <GetImagesRequest
     * xmlns="http://schemas.applab.org/2010/07/search"></GetImagesRequest>
     * 
     * Returns a response like: <?xml version="1.0"?> <GetImagesResponse
     * xmlns="http://schemas.applab.org/2010/07/search"> <image name="crops_apples_diseases_white_spots_on_leaves.jpg"
     * src="http://ckwapps.applab.org/downloads/images/crops_apples_diseases_white_spots_on_leaves.jpg"
     * md5hash="AO8SD123AASDAREKQWR23341ADSF"/> <image name="crops_apples_diseases_white_spots_on_leaves.jpg"
     * src="http://ckwapps.applab.org/downloads/images/crops_apples_diseases_white_spots_on_leaves.jpg"
     * md5hash="AO8SD123ASDAWQREKQWR23341ADSF"/> <image name="crops_apples_diseases_white_spots_on_leaves.jpg"
     * src="http://ckwapps.applab.org/downloads/images/crops_apples_diseases_white_spots_on_leaves.jpg"
     * md5hash="AO8SD123ASDAWQREKQWR23341ADSF"/> </GetImagesResponse>
     */

    @Override
    protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
            throws IOException, SAXException, ParserConfigurationException, ClassNotFoundException, SQLException {
        try {
            // Document requestXml = context.getRequestBodyAsXml();
            imageFilePath = ApplabConfiguration.getSearchImagesPath();
            writeResponse(context);
        }
        finally {
            context.close();
        }
    }

    public void writeResponse(ServletRequestContext context) throws IOException {
        context.writeXmlHeader();
        context.writeStartElement(RESPONSE_ELEMENT_NAME, NAMESPACE);

        // 1: Read directory
        File imagesDirectory = new File(imageFilePath);
        String imageBaseUrl = ApplabConfiguration.getSearchImagesBaseUrl();

        FilenameFilter jpgFilter = new FilenameFilter() {
            public boolean accept(File dir, String name) {
                return name.toLowerCase().endsWith(".jpg") || name.toLowerCase().endsWith(".jpeg");
            }
        };

        File[] children = imagesDirectory.listFiles(jpgFilter);
        if (children == null) {
            // Either dir does not exist or is not a directory
            // Do nothing, so there will be no image elements (this will result in deleting the images on the client!
        }
        else {
            HashMap<String, String> attributes = new HashMap<String, String>();
            for (int i = 0; i < children.length; i++) {
                try {
                    attributes.clear();

                    File child = children[i];
                    attributes.put(NAME_ATTRIBUTE_NAME, child.getName());

                    String sha1Hash = HashHelpers.createSHA1(child);
                    attributes.put(HASH_ATTRIBUTE_NAME, sha1Hash);

                    String url = imageBaseUrl + "/" + child.getName();
                    attributes.put(URL_ATTRIBUTE_NAME, url);

                    context.writeStartElement(IMAGE_ELEMENT_NAME, attributes);
                    context.writeEndElement();
                }
                catch (Exception e) {
                    log(e.getMessage());
                }
            }
        }
        context.writeEndElement();
    }
}
