/**
 *
 * Copyright (c) 2011 AppLab, Grameen Foundation
 *
 **/

package applab.search.feeds;

public class Forecast {
    private double lowTemperature;
    private double highTemperature;
    private String conditions;
    private double precipitation;
    private double maximumWindSpeed;
    private String windDirection;
    private String forecastDate;
    private double rainChance = -1;

    public Forecast() {
    }

    public double getLowTemperature() {
        return lowTemperature;
    }

    public void setLowTemperature(String lowTemperature) {        
        this.lowTemperature = convertStringToDouble(lowTemperature);
    }       

    public double getHighTemperature() {
        return highTemperature;
    }

    public void setHighTemperature(String highTemperature) {
        this.highTemperature = convertStringToDouble(highTemperature);
    }

    public String getConditions() {
        return conditions;
    }

    public void setConditions(String conditions) {
        this.conditions = conditions;
    }

    public double getPrecipitation() {
        return precipitation;
    }

    public void setPrecipitation(String precipitation) {
        this.precipitation = convertStringToDouble(precipitation);
    }

    public double getMaximumWindSpeed() {
        return maximumWindSpeed;
    }

    public void setMaximumWindSpeed(String maximumWindSpeed) {
        this.maximumWindSpeed = convertStringToDouble(maximumWindSpeed);
    }

    public String getWindDirection() {
        return windDirection;
    }

    public void setWindDirection(String windDirection) {
        this.windDirection = windDirection;
    }

    public String getForecastDate() {
        return forecastDate;
    }

    public void setForecastDate(String forecastDate) {
        this.forecastDate = forecastDate;
    }

    public double getRainChance() {
        return rainChance;
    }

    public void setRainChance(String rainChance) {
        try {
            this.rainChance = convertStringToDouble(rainChance);
        }
        catch (NumberFormatException exc) {

        }
    }
    private double convertStringToDouble(String value) {
        try {
            return Double.parseDouble(value);
        }
        catch (NumberFormatException ex) {
            return -1;
        }
    }
}
