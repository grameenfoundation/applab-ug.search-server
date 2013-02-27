/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.server;

import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import com.sforce.soap.enterprise.sobject.Attachment;
import java.io.PrintWriter;
import java.util.HashMap;
import java.util.Map.Entry;
import javax.servlet.ServletOutputStream;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

public class GetImagesFromSalesforce extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static HashMap<String, byte[]> cachedImages = new HashMap();

    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context) {
        try {
            String fullImageId = request.getParameter("imageId");
            if (fullImageId == null) {
                response.getWriter().write("Image Id is null so cannot get image");
                return;
            }
            String imageId = fullImageId.split("-")[1];
            log("Image Id : " + fullImageId);
            log("List of cached image ids");
            for (Map.Entry entry : cachedImages.entrySet()) {
                log((String)entry.getKey());
            }
            if (cachedImages.containsKey(fullImageId)) {
                log("Getting image from cache");
                response.setContentType("image/jpeg");
                response.setContentLength(((byte[])cachedImages.get(fullImageId)).length);
                response.getOutputStream().write((byte[])cachedImages.get(fullImageId));
            }
            else {
                log("Getting image from SF");

                SearchSalesforceProxy salesforceProxy = new SearchSalesforceProxy();
                Attachment attachment = salesforceProxy.getAttachement(imageId);
                if (attachment == null) {
                    log("Did not find any image with Id " + imageId);
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