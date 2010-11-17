package applab.search.server;

import java.rmi.RemoteException;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.rpc.ServiceException;

import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;

import com.sforce.soap.enterprise.fault.InvalidIdFault;
import com.sforce.soap.enterprise.fault.LoginFault;
import com.sforce.soap.enterprise.fault.UnexpectedErrorFault;

/**
 * Servlet implementation class UnsubscribeAgInfo
 */
public class UnsubscribeAgInfo extends ApplabServlet {
	private static final long serialVersionUID = 1L;
       

    public void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context) throws Exception {
        
        String phoneNumber = request.getParameter("phoneNumber");
        String message = request.getParameter("message");
        
        AgInfoSubscription subscription = new AgInfoSubscription(message, phoneNumber, false);
        String responseMessage = subscription.processAgInfoSubscription();
        log(responseMessage);
        response.getWriter().write(responseMessage);
    }
    
    public static String procesReg(String phoneNumber, String message)
            throws InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException, ServiceException {
        
        AgInfoSubscription subscription = new AgInfoSubscription(message, phoneNumber, false);
        return subscription.processAgInfoSubscription();
        
    }
}
