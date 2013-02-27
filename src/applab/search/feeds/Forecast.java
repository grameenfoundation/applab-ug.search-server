package applab.search.feeds;

public class Forecast
{
  private double lowTemperature;
  private double highTemperature;
  private String conditions;
  private double precipitation;
  private double maximumWindSpeed;
  private String windDirection;
  private String forecastDate;
  private double rainChance = -1.0D;

  public double getLowTemperature()
  {
    return this.lowTemperature;
  }

  public void setLowTemperature(String lowTemperature) {
    this.lowTemperature = Double.parseDouble(lowTemperature);
  }

  public double getHighTemperature() {
    return this.highTemperature;
  }

  public void setHighTemperature(String highTemperature) {
    this.highTemperature = Double.parseDouble(highTemperature);
  }

  public String getConditions() {
    return this.conditions;
  }

  public void setConditions(String conditions) {
    this.conditions = conditions;
  }

  public double getPrecipitation() {
    return this.precipitation;
  }

  public void setPrecipitation(String precipitation) {
    this.precipitation = Double.parseDouble(precipitation);
  }

  public double getMaximumWindSpeed() {
    return this.maximumWindSpeed;
  }

  public void setMaximumWindSpeed(String maximumWindSpeed) {
    this.maximumWindSpeed = Double.parseDouble(maximumWindSpeed);
  }

  public String getWindDirection() {
    return this.windDirection;
  }

  public void setWindDirection(String windDirection) {
    this.windDirection = windDirection;
  }

  public String getForecastDate() {
    return this.forecastDate;
  }

  public void setForecastDate(String forecastDate) {
    this.forecastDate = forecastDate;
  }

  public double getRainChance() {
    return this.rainChance;
  }

  public void setRainChance(String rainChance) {
    try {
      this.rainChance = Double.parseDouble(rainChance);
    }
    catch (NumberFormatException localNumberFormatException)
    {
    }
  }
}