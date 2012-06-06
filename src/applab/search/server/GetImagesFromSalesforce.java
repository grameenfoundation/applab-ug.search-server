package applab.search.server;

import java.util.HashMap;
import java.util.Map.Entry;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import applab.server.ApplabConfiguration;
import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import applab.server.WebAppId;

import com.sforce.soap.enterprise.LoginResult;
import com.sforce.soap.enterprise.QueryResult;
import com.sforce.soap.enterprise.SessionHeader;
import com.sforce.soap.enterprise.SforceServiceLocator;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.enterprise.sobject.Attachment;

public class GetImagesFromSalesforce extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static HashMap<String, byte[]> cachedImages = new HashMap<String, byte[]>();

    @Override
    protected void doApplabGet(HttpServletRequest request,
                               HttpServletResponse response, ServletRequestContext context) {
        try {
            // string is split since it contains both attachment Id and menu Item Id
            String fullImageId = request.getParameter("imageId");
            if (fullImageId == null) {
                response.getWriter().write("Image Id is null so cannot get image");
                return;
            }
            String imageId = fullImageId.split("-")[1];
            log("Image Id : " + fullImageId);
            log("List of cached image ids");
            for (Entry<String, byte[]> entry : cachedImages.entrySet()) {
                log(entry.getKey());
            }
            if (cachedImages.containsKey(fullImageId)) {
                log("Getting image from cache");
                response.setContentType("image/jpeg");
                response.setContentLength(cachedImages.get(fullImageId).length);
                response.getOutputStream().write(cachedImages.get(fullImageId));
            }
            else {
                log("Getting image from SF");

                SearchSalesforceProxy salesforceProxy = new SearchSalesforceProxy();
                Attachment attachment = salesforceProxy.getAttachement(imageId);
                if (attachment == null) {

                    log("Did not find any image with Id " + imageId);
                    // TODO: Improve this to provide a more descriptive error to the client
                    response.getOutputStream().write(null);
                }
                else {
                    cachedImages.put(fullImageId, attachment.getBody());
                    response.setContentType("image/jpeg");
                    response.setContentLength(attachment.getBody().length);
                    response.getOutputStream().write(attachment.getBody());
                }
            }
        }
        catch (Exception ex) {
            log("Failed to get image");
            log(ex.getMessage());
        }

    }
}
