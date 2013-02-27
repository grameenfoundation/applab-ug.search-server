package applab.search.server;

import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.HashHelpers;
import applab.server.ServletRequestContext;
import java.io.File;
import java.io.FilenameFilter;
import java.io.IOException;
import java.sql.SQLException;
import java.util.HashMap;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.parsers.ParserConfigurationException;
import org.xml.sax.SAXException;

public class GetImages extends ApplabServlet
{
  private static final long serialVersionUID = 1L;
  public static final String NAMESPACE = "http://schemas.applab.org/2010/07/search";
  private static final String RESPONSE_ELEMENT_NAME = "GetImagesResponse";
  private static final String NAME_ATTRIBUTE_NAME = "name";
  private static final String URL_ATTRIBUTE_NAME = "src";
  private static final String HASH_ATTRIBUTE_NAME = "sha1hash";
  private static final String IMAGE_ELEMENT_NAME = "image";
  public static String imageFilePath = null;

  protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws IOException, SAXException, ParserConfigurationException, ClassNotFoundException, SQLException
  {
    try
    {
      imageFilePath = (String)ApplabConfiguration.getConfigParameter(context.getContextName(), "searchImagesPath", "/var/www/vhosts/default/htdocs/search.images");
      writeResponse(context);
    }
    finally {
      context.close();
    }
  }

  public void writeResponse(ServletRequestContext context) throws IOException {
    context.writeXmlHeader();
    context.writeStartElement("GetImagesResponse", "http://schemas.applab.org/2010/07/search");

    File imagesDirectory = new File(imageFilePath);
    String imageBaseUrl = (String)ApplabConfiguration.getConfigParameter(context.getContextName(), "searchImagesBaseUrl", "");

    FilenameFilter jpgFilter = new FilenameFilter() {
      public boolean accept(File dir, String name) {
        return (name.toLowerCase().endsWith(".jpg")) || (name.toLowerCase().endsWith(".jpeg"));
      }
    };
    File[] children = imagesDirectory.listFiles(jpgFilter);
    if (children != null)
    {
      HashMap attributes = new HashMap();
      for (int i = 0; i < children.length; i++) {
        try {
          attributes.clear();

          File child = children[i];
          attributes.put("name", child.getName());

          String sha1Hash = HashHelpers.createSHA1(child);
          attributes.put("sha1hash", sha1Hash);

          String url = imageBaseUrl + "/" + child.getName();
          attributes.put("src", url);

          context.writeStartElement("image", attributes);
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