/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.server;

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
import java.io.PrintWriter;
import java.util.HashMap;
import java.util.Map.Entry;
import javax.servlet.ServletOutputStream;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.namespace.QName;

public class GetImagesFromSalesforce extends ApplabServlet {
    private static final long serialVersionUID = 1L;
    private static HashMap<String, byte[]> cachedImages = new HashMap();

    protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context) throws Exception {
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
            SforceServiceLocator soapServiceLocator = new SforceServiceLocator();
            soapServiceLocator
                    .setSoapEndpointAddress((String)
                    ApplabConfiguration.getConfigParameter(WebAppId.global,
                            "salesforceAddress", ""));
            SoapBindingStub binding = (SoapBindingStub)soapServiceLocator
                    .getSoap();
            LoginResult loginResult = binding.login(
                    (String)ApplabConfiguration.getConfigParameter(
                            WebAppId.global, "salesforceUsername", ""),
                    (String)ApplabConfiguration.getConfigParameter(WebAppId.global, "salesforcePassword", "") +
                            (String)ApplabConfiguration.getConfigParameter(
                                    WebAppId.global, "salesforceToken", ""));
            SessionHeader sessionHeader = new SessionHeader(
                    loginResult.getSessionId());
            binding.setHeader(soapServiceLocator.getServiceName()
                    .getNamespaceURI(), "SessionHeader", sessionHeader);
            QueryResult queryResults = binding
                    .query("Select Id, Body from Attachment where Id = '" +
                            imageId + "'");
            if (queryResults.getSize() > 0) {
                for (int i = 0; i < queryResults.getRecords().length; i++) {
                    Attachment attachment = (Attachment)queryResults
                            .getRecords()[i];
                    cachedImages.put(fullImageId, attachment.getBody());
                    response.setContentType("image/jpeg");
                    response.setContentLength(attachment.getBody().length);
                    response.getOutputStream().write(attachment.getBody());
                }
            }
        }
    }
}