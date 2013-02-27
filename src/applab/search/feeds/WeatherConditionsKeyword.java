package applab.search.feeds;

import applab.server.DatabaseHelpers;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;

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
    private double relativeHumidity = -1.0D;
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
        this.forecasts = new ArrayList();
    }

    public Forecast getCurrentForecast() {
        if (!this.forecasts.isEmpty()) {
            try {
                Calendar currentConditionsDate = Calendar.getInstance();

                currentConditionsDate.setTime(DatabaseHelpers.getJavaDateFromString(getReadingTime(), 4));

                for (Forecast forecast : this.forecasts) {
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
        return this.forecasts;
    }

    public void setForecasts(List<Forecast> forecasts) {
        this.forecasts = forecasts;
    }

    public String getBaseKeyword() {
        return this.baseKeyword;
    }

    public void setBaseKeyword(String baseKeyword) {
        this.baseKeyword = baseKeyword;
    }

    public String getAttribution() {
        return this.attribution;
    }

    public void setAttribution(String attribution) {
        this.attribution = attribution;
    }

    public int getCategoryId() {
        return this.categoryId;
    }

    public void setCategoryId(int categoryId) {
        this.categoryId = categoryId;
    }

    public String getReadingTime() {
        return this.readingTime;
    }

    public void setReadingTime(String readingTime) {
        this.readingTime = readingTime;
    }

    public String getTemperature() {
        return this.temperature;
    }

    public void setTemperature(String temperature) {
        this.temperature = temperature;
    }

    public String getFeelsLike() {
        return this.feelsLike;
    }

    public void setFeelsLike(String feelsLike) {
        this.feelsLike = feelsLike;
    }

    public String getWindSpeedMax() {
        return this.windSpeedMax;
    }

    public void setWindSpeedMax(String windSpeedMax) {
        this.windSpeedMax = windSpeedMax;
    }

    public String getWindDirection() {
        return this.windDirection;
    }

    public void setWindDirection(String windDirection) {
        this.windDirection = windDirection;
    }

    public String getConditions() {
        return this.conditions;
    }

    public void setConditions(String conditions) {
        this.conditions = conditions;
    }

    public double getRelativeHumidity() {
        return this.relativeHumidity;
    }

    public void setRelativeHumidity(String relativeHumidity) {
        try {
            this.relativeHumidity = Double.parseDouble(relativeHumidity);
        }
        catch (NumberFormatException localNumberFormatException) {
        }
    }

    public String getAirpressure() {
        return this.airpressure;
    }

    public void setAirpressure(String airpressure) {
        this.airpressure = airpressure;
    }

    public String getVisibility() {
        return this.visibility;
    }

    public void setVisibility(String visibility) {
        this.visibility = visibility;
    }

    public String getRegion() {
        return this.region;
    }

    public void setRegion(String region) {
        this.region = region;
    }

    public String getDistrict() {
        return this.district;
    }

    public void setDistrict(String district) {
        this.district = district;
    }

    public String getSubcounty() {
        return this.subcounty;
    }

    public void setSubcounty(String subcounty) {
        this.subcounty = subcounty;
    }

    public String getLocationId() {
        return this.locationId;
    }

    public void setLocationId(String locationId) {
        this.locationId = locationId;
    }

    public String generateKeyword()
    {
        if ((this.region == null) || (this.district == null) || (this.subcounty == null)) {
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
            return getWeatherConditions() + getForecastConditions();
        }
        catch (Exception exc) {
        }
        return null;
    }

    public String getWeatherConditions() throws ParseException {
        Forecast currentForecast = getCurrentForecast();
        StringBuilder keywordContent = new StringBuilder();

        Calendar currentConditionsDate = Calendar.getInstance();
        currentConditionsDate.setTime(DatabaseHelpers.getJavaDateFromString(getReadingTime(), 4));

        if (getReadingTime() != "") {
            keywordContent.append("Weather: Today " + DatabaseHelpers.formatDateTime(currentConditionsDate.getTime(), 2) + ", ");
        }
        else {
            return null;
        }

        if (getConditions().contains("stormy")) {
            keywordContent.append("rainy and windy. ");
        }
        else {
            keywordContent.append(getConditions() + ". ");
        }

        keywordContent.append("Temperatures  : Low - High = " + String.valueOf(currentForecast.getLowTemperature()) + "C - "
                + String.valueOf(currentForecast.getHighTemperature()) + "C" + ". ");

        String rainChance = "";
        if ((getConditions().contains("rain")) || (getConditions().contains("storm"))) {
            if ((currentForecast.getRainChance() < 100.0D) && (currentForecast.getRainChance() >= 0.0D)) {
                if (currentForecast.getRainChance() > 50.0D) {
                    rainChance = "high";
                }
                else {
                    rainChance = "low";
                }
            }

            if (rainChance != "") {
                keywordContent.append("There is a " + rainChance + " chance of rain.      ");
            }
            else if (currentForecast.getPrecipitation() > 0.0D) {
                keywordContent.append("There is a high chance of rain.      ");
            }
        }
        return keywordContent.toString();
    }

    public String getForecastConditions() throws ParseException {
        StringBuilder forecastContent = new StringBuilder();

        Calendar currentConditionsDate = Calendar.getInstance();
        currentConditionsDate.setTime(DatabaseHelpers.getJavaDateFromString(getReadingTime(), 4));

        Calendar forecastDate = Calendar.getInstance();
        int iterations = 0;

        for (Forecast forecast : getForecasts()) {
            if (iterations > 5) {
                break;
            }
            if (forecast.getForecastDate() != "") {
                forecastDate.setTime(DatabaseHelpers.getJavaDateFromString(forecast.getForecastDate(), 3));

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
                    if ((forecast.getConditions().contains("rain")) || (forecast.getConditions().contains("storm"))) {
                        if ((forecast.getRainChance() < 100.0D) && (forecast.getRainChance() >= 0.0D)) {
                            if (forecast.getRainChance() > 50.0D) {
                                rainChance = "high";
                            }
                            else {
                                rainChance = "low";
                            }

                        }

                        if (rainChance != "") {
                            forecastContent.append("There is a " + rainChance + " chance of rain.      ");
                        }
                        else if (forecast.getPrecipitation() > 0.0D) {
                            forecastContent.append("There is a high chance of rain.      ");
                        }
                    }

                }
                iterations++;
            }
        }
        return forecastContent.toString();
    }
}