/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.feeds;

import java.text.DecimalFormat;

public class KeywordEntry {
    private String attribution;
    private String baseKeyword;
    private String market;
    private String region;
    private String product;
    private String unit;
    private String wholesalePrice;
    private String retailPrice;
    private String date;
    private int categoryId;

    public KeywordEntry(String attribution, String baseKeyword, int categoryId) {
        this.attribution = attribution;
        this.baseKeyword = baseKeyword;
        this.categoryId = categoryId;
    }

    public String getMarket() {
        return this.market;
    }

    public void setMarket(String market) {
        this.market = market;
    }

    public String getProduct() {
        return this.product;
    }

    public void setProduct(String product) {
        this.product = product;
    }

    public String getUnit() {
        return this.unit;
    }

    public void setUnit(String unit) {
        this.unit = unit;
    }

    public String getWholesalePrice() {
        return this.wholesalePrice;
    }

    public void setWholesalePrice(String wholesalePrice) {
        this.wholesalePrice = wholesalePrice;
    }

    public String getRetailPrice() {
        return this.retailPrice;
    }

    public void setRetailPrice(String retailPrice) {
        this.retailPrice = retailPrice;
    }

    public String getDate() {
        return this.date;
    }

    public void setDate(String date) {
        this.date = date;
    }

    public String getBaseKeyword() {
        return this.baseKeyword;
    }

    public void setBaseKeyword(String baseKeyword) {
        this.baseKeyword = baseKeyword;
    }

    public String getRegion() {
        return this.region;
    }

    public void setRegion(String region) {
        this.region = region;
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

    public String generateKeyword()
    {
        if (this.region == null) {
            return null;
        }

        StringBuilder keyword = new StringBuilder();
        keyword.append(this.baseKeyword);
        keyword.append(" ");
        keyword.append(this.region);
        keyword.append(" ");
        keyword.append(this.market.replace(" ", "_"));
        keyword.append(" ");
        keyword.append(this.product.replace(" ", "_"));
        return keyword.toString();
    }

    public String getContent()
    {
        if ((this.retailPrice == null) && (this.wholesalePrice == null)) {
            return null;
        }
        if (this.retailPrice == null) {
            return getWholesaleContent();
        }
        if (this.wholesalePrice == null) {
            return getRetailContent();
        }
        return getRetailContent() + " \n" + getWholesaleContent();
    }

    private String getRetailContent() {
        String price = "";
        if (this.retailPrice != null) {
            price = "Retail Price: " + formatNumber(Double.valueOf(this.retailPrice).doubleValue()) + " " + getUnitSegment() + ".";
        }
        return price;
    }

    private String getWholesaleContent() {
        String price = "";
        if (this.wholesalePrice != null) {
            price = "Wholesale Price: " + formatNumber(Double.valueOf(this.wholesalePrice).doubleValue()) + " " + getUnitSegment() + ".";
        }
        return price;
    }

    private String getUnitSegment() {
        return "Shs per " + this.unit;
    }

    private String formatNumber(double number) {
        DecimalFormat formatter = new DecimalFormat("#,###,###");
        return formatter.format(number);
    }
}