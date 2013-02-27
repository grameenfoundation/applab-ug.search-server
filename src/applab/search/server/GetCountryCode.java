package applab.search.server;

import applab.server.ApplabServlet;
import applab.server.ServletRequestContext;
import java.io.IOException;
import java.io.PrintWriter;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.xml.rpc.ServiceException;

public class GetCountryCode extends ApplabServlet
{
  private static final long serialVersionUID = 1L;
  private static final String IMEI = "x-Imei";
  private static final String COUNTRY_CODE = "CountryCode";

  protected void doApplabGet(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws ServletException, IOException, ServiceException
  {
    log("Reached get method for get country code");
    doApplabPost(request, response, context);
  }

  protected void doApplabPost(HttpServletRequest request, HttpServletResponse response, ServletRequestContext context)
    throws ServletException, IOException, ServiceException
  {
    log("Reached post method for get coutry code");
    String imei = request.getHeader("x-Imei");
    log("x-Imei: " + imei);

    String countryCode = getCountryCodeFromSalesforce(imei);

    String countryCodeJson = String.format("{\"countryCode\" : \"%s\"}", new Object[] { countryCode });

    PrintWriter out = response.getWriter();
    out.println(countryCodeJson);
    log("Finished sending country code");
  }

  private String getCountryCodeFromSalesforce(String imei)
  {
    try
    {
      SearchSalesforceProxy searchSaleforceProxy = new SearchSalesforceProxy();
      String countryCode = searchSaleforceProxy.getCountryCode(imei);
      log("Country code for IMEI " + imei + " is: " + countryCode);
      return countryCode;
    }
    catch (Exception e) {
      log("Failed to get county code from salesforce");
      e.printStackTrace();
    }
    return null;
  }
}