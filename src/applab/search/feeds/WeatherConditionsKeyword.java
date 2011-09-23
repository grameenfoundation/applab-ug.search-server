package applab.search.feeds;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import applab.server.DatabaseHelpers;

/**
 * Weather Conditions Keyword Class
 */
public class WeatherConditionsKeyword {
    private String attribution;
    private String baseKeyword;
    private int categoryId;
    private String locationId;
    private String readingTime;
    private String temperature;
    private String feelsLike;
    private String windSpeedMax;
    private String windDirection;
    private String conditions;
    private double relativeHumidity = -1;
    private String airpressure;
    private String visibility;

    private String region;
    private String district;
    private String subcounty;
    private List<Forecast> forecasts;

    public WeatherConditionsKeyword() {
        initKeyword();
    }

    public WeatherConditionsKeyword(String attribution, String baseKeyword, int categoryId, String locationId, String subcounty,
            String district, String region) {

        this.attribution = attribution;
        this.baseKeyword = baseKeyword;
        this.categoryId = categoryId;
        this.locationId = locationId;
        this.subcounty = subcounty;
        this.district = district;
        this.region = region;

        initKeyword();
    }

    private void initKeyword() {
        this.forecasts = new ArrayList<Forecast>();
    }

    /***
     * 
     * @return - Forecast with same date as today.
     */
    public Forecast getCurrentForecast() {

        if (!this.forecasts.isEmpty()) {
            try {
                Calendar currentConditionsDate = Calendar.getInstance();

                currentConditionsDate.setTime(DatabaseHelpers.getJavaDateFromString(this.getReadingTime(), 4));

                for (Forecast forecast : forecasts) {
                    Calendar forecastCalendar = Calendar.getInstance();
                    forecastCalendar.setTime(DatabaseHelpers.getJavaDateFromString(forecast.getForecastDate(), 3));

                    if (DatabaseHelpers.compareCalendarDatesIgnoreTime(currentConditionsDate, forecastCalendar) == 0) {
                        return forecast;
                    }
                }

            }
            catch (ParseException e) {
                e.printStackTrace();
                return null;
            }
        }
        return null;

    }

    public List<Forecast> getForecasts() {
        return forecasts;
    }

    public void setForecasts(List<Forecast> forecasts) {
        this.forecasts = forecasts;
    }

    public String getBaseKeyword() {
        return baseKeyword;
    }

    public void setBaseKeyword(String baseKeyword) {
        this.baseKeyword = baseKeyword;
    }

    public String getAttribution() {
        return attribution;
    }

    public void setAttribution(String attribution) {
        this.attribution = attribution;
    }

    public int getCategoryId() {
        return categoryId;
    }

    public void setCategoryId(int categoryId) {
        this.categoryId = categoryId;
    }

    public String getReadingTime() {
        return readingTime;
    }

    public void setReadingTime(String readingTime) {
        this.readingTime = readingTime;
    }

    public String getTemperature() {
        return temperature;
    }

    public void setTemperature(String temperature) {
        this.temperature = temperature;
    }

    public String getFeelsLike() {
        return feelsLike;
    }

    public void setFeelsLike(String feelsLike) {
        this.feelsLike = feelsLike;
    }

    public String getWindSpeedMax() {
        return windSpeedMax;
    }

    public void setWindSpeedMax(String windSpeedMax) {
        this.windSpeedMax = windSpeedMax;
    }

    public String getWindDirection() {
        return windDirection;
    }

    public void setWindDirection(String windDirection) {
        this.windDirection = windDirection;
    }

    public String getConditions() {
        return conditions;
    }

    public void setConditions(String conditions) {
        this.conditions = conditions;
    }

    public double getRelativeHumidity() {
        return relativeHumidity;
    }

    public void setRelativeHumidity(String relativeHumidity) {
        try {
            this.relativeHumidity = Double.parseDouble(relativeHumidity);
        }
        catch (NumberFormatException exc) {

        }
    }

    public String getAirpressure() {
        return airpressure;
    }

    public void setAirpressure(String airpressure) {
        this.airpressure = airpressure;
    }

    public String getVisibility() {
        return visibility;
    }

    public void setVisibility(String visibility) {
        this.visibility = visibility;
    }

    public String getRegion() {
        return region;
    }

    public void setRegion(String region) {
        this.region = region;
    }

    public String getDistrict() {
        return district;
    }

    public void setDistrict(String district) {
        this.district = district;
    }

    public String getSubcounty() {
        return subcounty;
    }

    public void setSubcounty(String subcounty) {
        this.subcounty = subcounty;
    }

    public String getLocationId() {
        return locationId;
    }

    public void setLocationId(String locationId) {
        this.locationId = locationId;
    }

    public String generateKeyword() {

        if (this.region == null || this.region =="" || this.district == null || this.district == "" || this.subcounty == null|| this.subcounty == "") {
            return null;
        }
        StringBuilder keyword = new StringBuilder();
        keyword.append(this.baseKeyword);
        keyword.append(" ");
        keyword.append(this.region.replace(" ", "_"));
        keyword.append(" ");
        keyword.append(this.district.replace(" ", "_"));
        keyword.append(" ");
        keyword.append(this.subcounty.replace(" ", "_"));
        return keyword.toString();
    }

    public String getContent() {
        try {
            return this.getWeatherConditions() + this.getForecastConditions();
        }
        catch (Exception exc) {
            return null;
        }

    }

    /**
     * Generates Current Weather conditions. TODO: Still doing some formating on the keyword content. any suggestions
     * are welcome.
     * 
     * @return - String containing the current weather content
     * 
     * @throws ParseException
     */
    public String getWeatherConditions() throws ParseException {

        Forecast currentForecast = this.getCurrentForecast();
        StringBuilder keywordContent = new StringBuilder();

        Calendar currentConditionsDate = Calendar.getInstance();
        currentConditionsDate.setTime(DatabaseHelpers.getJavaDateFromString(this.getReadingTime(), 4));

        if (this.getReadingTime() != "") {

            keywordContent.append("Weather: Today " + DatabaseHelpers.formatDateTime(currentConditionsDate.getTime(), 2) + ", "
                    );

        }
        else {
            return null;
        }

        if (this.getConditions().contains("stormy")) {
            keywordContent.append("rainy and windy. ");

        }
        else {
            keywordContent.append(this.getConditions() + ". ");
        }

        keywordContent.append("Temperatures  : Low - High = " + String.valueOf(currentForecast.getLowTemperature()) + "C - "
                + String.valueOf(currentForecast.getHighTemperature()) + "C" + ". ");

        String rainChance = "";
        if (this.getConditions().contains("rain") || this.getConditions().contains("storm")) {

            // In case rain chance comes in through the feed
            if (currentForecast.getRainChance() < 100 && currentForecast.getRainChance() >= 0) {
                if (currentForecast.getRainChance() > 50) {
                    rainChance = "high";
                }
                else {
                    rainChance = "low";
                }
            }

            // In case the rain chance does not come in through the feed, use a high value when precipitation is > 0mm
            if (rainChance != "") {
                keywordContent.append("There is a " + rainChance + " chance of rain.      ");
            }
            else {
                if (currentForecast.getPrecipitation() > 0) {
                    keywordContent.append("There is a high chance of rain.      ");
                }
            }
        }

        return keywordContent.toString();
    }

    /**
     * Generates Forecast conditions
     * 
     * @return - String containing the weather forecast content
     * 
     * @throws ParseException
     */
    public String getForecastConditions()
            throws ParseException {

        StringBuilder forecastContent = new StringBuilder();

        Calendar currentConditionsDate = Calendar.getInstance();
        currentConditionsDate.setTime(DatabaseHelpers.getJavaDateFromString(this.getReadingTime(), 4));

        Calendar forecastDate = Calendar.getInstance();
        int iterations = 0;

        for (Forecast forecast : this.getForecasts()) {
            // Only take forecasts for the next 5 days
            if (iterations > 5) {
                break;
            }

            if (forecast.getForecastDate() != "") {
                forecastDate.setTime(DatabaseHelpers.getJavaDateFromString(forecast.getForecastDate(), 3));
            }
            else {
                continue;
            }

            if (DatabaseHelpers.compareCalendarDatesIgnoreTime(forecastDate, currentConditionsDate) > 0) {

                forecastContent.append(DatabaseHelpers.formatDateTime(forecastDate.getTime(), 2) + ", ");

                if (forecast.getConditions().contains("stormy")) {
                    forecastContent.append("rainy and windy. ");

                }
                else {
                    forecastContent.append(forecast.getConditions() + ". ");
                }

                forecastContent.append("Temperatures : Low - High = " + String.valueOf(forecast.getLowTemperature()) + "C - "
                        + String.valueOf(forecast.getHighTemperature()) + "C. ");

                String rainChance = "";
                if (forecast.getConditions().contains("rain") || forecast.getConditions().contains("storm")) {

                    // In case rain chance comes in through the feed
                    if (forecast.getRainChance() < 100 && forecast.getRainChance() >= 0) {
                        if (forecast.getRainChance() > 50) {
                            rainChance = "high";
                        }
                        else {
                            rainChance = "low";
                        }
                    }

                    // In case the rain chance does not come in through the feed, use a high value when precipitation is
                    // > 0mm
                    if (rainChance != "") {
                        forecastContent.append("There is a " + rainChance + " chance of rain.      ");
                    }
                    else {
                        if (forecast.getPrecipitation() > 0) {
                            forecastContent.append("There is a high chance of rain.      ");
                        }
                    }

                }
            }
            iterations = iterations + 1;
        }
        return forecastContent.toString();
    }

}