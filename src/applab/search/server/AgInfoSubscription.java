package applab.search.server;


import java.rmi.RemoteException;
import java.util.ArrayList;
import java.util.HashMap;

import javax.xml.rpc.ServiceException;

import applab.Person;
import applab.Phone;
import applab.server.SalesforceProxy;

import com.sforce.soap.enterprise.DeleteResult;
import com.sforce.soap.enterprise.QueryResult;
import com.sforce.soap.enterprise.SaveResult;
import com.sforce.soap.enterprise.SoapBindingStub;
import com.sforce.soap.enterprise.fault.InvalidFieldFault;
import com.sforce.soap.enterprise.fault.InvalidIdFault;
import com.sforce.soap.enterprise.fault.InvalidQueryLocatorFault;
import com.sforce.soap.enterprise.fault.InvalidSObjectFault;
import com.sforce.soap.enterprise.fault.LoginFault;
import com.sforce.soap.enterprise.fault.MalformedQueryFault;
import com.sforce.soap.enterprise.fault.UnexpectedErrorFault;
import com.sforce.soap.enterprise.sobject.Person_Group_Association__c;
import com.sforce.soap.enterprise.sobject.Person__c;
import com.sforce.soap.enterprise.sobject.Subscription_Topic__c;

public class AgInfoSubscription {

    private final String NO_TOPICS_SUBSCRIBE_STRING = "There are no topics to subscribe to at the moment.";
    private final String NO_TOPICS_UNSUBSCRIBE_STRING = "There are no topics to unsubscribe to at the moment.";
    
    private final String TOPICS_INFO_SUBSCRIBE_STRING = "Please select TOPICs from list below. For muliple select, seperate with commas e.g. sub 1,4 and send to 178";
    private final String TOPICS_INFO_UNSUBSCRIBE_STRING = "Please select TOPICs from list below. For muliple select, seperate with commas e.g. unsub 1,4 and send to 178";
    
    private final String SUCCESS_HEADER_SUBSCRIBE_STRING = "You have subscribed to the following channels:";
    private final String SUCCESS_HEADER_UNSUBSCRIBE_STRING = "You have unsubscribed to the following channels:";
    
    private final String FAIL_HEADER_SUBSCRIBE_STRING = "You have already subscribed to the following channels:";
    private final String FAIL_HEADER_UNSUBSCRIBE_STRING = "You have already unsubscribed to the following channels:";

    private final String NO_PERSON_SUBSCRIBE = "Your phone has not been recoginised. Not allowed to subscribe to topics";
    private final String NO_PERSON_UNSUBSCRIBE = "Your phone has not been recoginised. Not allowed to unsubscribe to topics";

    private final String FAIL_SUBSCRIBE = "We have failed to subscribe you to any topics.  Please try again";
    private final String FAIL_UNSUBSCRIBE = "We have failed to unsubscribe you to any topics.  Please try again";

    private final String NEW_LINE_STRING = "\n";
    
    private HashMap<String, Subscription_Topic__c> topicsToAdd;
    private ArrayList<String> groupsToChange;
    private ArrayList<String> groupsNotChanged;
    
    private String requestParameter;
    private String phoneNumber;

    // True = subscription, False = unsubscription
    private boolean subscribe;

    public AgInfoSubscription(String requestParameter, String phoneNumber, boolean subscribe) {

        this.requestParameter = requestParameter;
        this.phoneNumber = phoneNumber;
        this.subscribe = subscribe;
        this.groupsToChange = new ArrayList<String>(); 
        this.groupsNotChanged = new ArrayList<String>(); 
    }

    public String processAgInfoSubscription()
            throws InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException, ServiceException {

        if (this.requestParameter == "") {
            return getAllTopicsString();
        }
        else {
            String[] paramArray =  this.requestParameter.split(",");
            if (checkInputIsNumber(paramArray[0])) {
                this.topicsToAdd = getTopicGroupsFromDisplayOrder(paramArray);
                if (this.topicsToAdd.size() < 1) {
                    return "You have not entered any valid topics. Please try again." + this.NEW_LINE_STRING + getAllTopicsString();
                }
            }
            else {
                this.topicsToAdd = getTopicGroupsFromName(paramArray);
                if (this.topicsToAdd.size() < 1) {
                    return "You have not entered any valid topics. Please try again." + this.NEW_LINE_STRING + getAllTopicsString();
                }
            }
        }

        // Get the details for the person. Create one if required.
        Person person = getPersonDetails(this.phoneNumber);

        // If the person has been created then we can create the subscriptions
        boolean success = false;
        if (person != null) {
            if (this.subscribe) {
                success = addPersonGroupAssociations(person);
            }
            else {
                success = removePersonGroupAssociation(person); 
            }
        }
        else {
            if (this.subscribe) {
                return this.NO_PERSON_SUBSCRIBE;
            }
            else {
                return this.NO_PERSON_UNSUBSCRIBE;
            }
        }

        // Return a useful response message to the subscriber.
        if (success) {
            return parseSuccessMessage();
        }
        else {
            if (this.subscribe) {
                return this.FAIL_SUBSCRIBE;
            }
            else {
                return this.FAIL_UNSUBSCRIBE;
            }
        }
    }

    // Get the topics that the person wants to subscribe to. From the display order
    private HashMap<String, Subscription_Topic__c> getTopicGroupsFromDisplayOrder(String[] topicDisplayOrder)
            throws InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException, ServiceException {

        String params = generateQueryParams(topicDisplayOrder);
        SoapBindingStub binding = SalesforceProxy.createBinding();
        StringBuilder queryText = new StringBuilder();
        queryText.append("SELECT ");
        queryText.append("Name, Group__c ");
        queryText.append("FROM ");
        queryText.append("Subscription_Topic__c ");
        queryText.append("WHERE ");
        queryText.append("Short_Code__c IN (");
        queryText.append(params);
        queryText.append(") ");
        queryText.append("AND Status__c = 'Active' ");
        queryText.append("ORDER BY Display_Order__c");
        QueryResult query = binding.query(queryText.toString());

        HashMap<String, Subscription_Topic__c> topics = new HashMap<String, Subscription_Topic__c>();
        if (query.getSize() > 0) {
            for (int i = 0; i < query.getSize(); i++) {
                Subscription_Topic__c subscriptionTopic = (Subscription_Topic__c) query.getRecords(i);
                topics.put(subscriptionTopic.getGroup__c(), subscriptionTopic);
            }
        }
        
        return topics;
    }

    // Get the topics from the topic name
    private HashMap<String, Subscription_Topic__c> getTopicGroupsFromName(String[] topicNames)
            throws InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException, ServiceException {

        String params = generateQueryParams(topicNames);
        SoapBindingStub binding = SalesforceProxy.createBinding();
        StringBuilder queryText = new StringBuilder();
        queryText.append("SELECT ");
        queryText.append("Name, Group__c ");
        queryText.append("FROM ");
        queryText.append("Subscription_Topic__c ");
        queryText.append("WHERE ");
        queryText.append("Name IN (");
        queryText.append(params);
        queryText.append(") ");
        queryText.append("AND Status__c = 'Active' ");
        queryText.append("ORDER BY Display_Order__c");
        QueryResult query = binding.query(queryText.toString());

        HashMap<String, Subscription_Topic__c> topics = new HashMap<String, Subscription_Topic__c>();
        if (query.getSize() > 0) {
            for (int i = 0; i < query.getSize(); i++) {
                Subscription_Topic__c subscriptionTopic = (Subscription_Topic__c) query.getRecords(i);
                topics.put(subscriptionTopic.getGroup__c(), subscriptionTopic);
            }
        }
        return topics;
    }

    // Get all the topics
    private String getAllTopicsString()
            throws InvalidSObjectFault, MalformedQueryFault, InvalidFieldFault, InvalidIdFault, UnexpectedErrorFault, InvalidQueryLocatorFault, RemoteException, ServiceException {

        SoapBindingStub binding = SalesforceProxy.createBinding();
        StringBuilder queryText = new StringBuilder();
        queryText.append("SELECT ");
        queryText.append("Name, Short_Code__c ");
        queryText.append("FROM ");
        queryText.append("Subscription_Topic__c ");
        queryText.append("WHERE Status__c = 'Active' ");
        queryText.append("ORDER BY Display_Order__c");
        QueryResult query = binding.query(queryText.toString());

        StringBuilder textMessage = new StringBuilder();
        if (query.getSize() > 0) {
            if (this.subscribe) {
                textMessage.append(TOPICS_INFO_SUBSCRIBE_STRING);
            }
            else {
                textMessage.append(TOPICS_INFO_UNSUBSCRIBE_STRING);
            }
            textMessage.append(NEW_LINE_STRING);
            for (int i = 0; i < query.getSize(); i++) {
                Subscription_Topic__c subscriptionTopic = (Subscription_Topic__c) query.getRecords(i);
                textMessage.append(subscriptionTopic.getShort_Code__c());
                textMessage.append(" = ");
                textMessage.append(subscriptionTopic.getName());
                textMessage.append(NEW_LINE_STRING);
            }
        }
        else {
            if (this.subscribe) {
                textMessage.append(NO_TOPICS_SUBSCRIBE_STRING);
            }
            else {
                textMessage.append(NO_TOPICS_UNSUBSCRIBE_STRING);
            }
        }
        return textMessage.toString();
    }

    // Get the person__c details from the phone number. Can it be the imei?
    private Person getPersonDetails(String phoneNumber)
            throws ServiceException, InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException {

        
        // Load the Person
        try {
            return Person.loadPhone(phoneNumber);
        }
        catch (RemoteException e) {
            if (this.subscribe) {
                return createNewPerson(phoneNumber);
            }
            else {
                return null;
            }
        }
    }
    
    // Create a new person if the subscription is from a phone we don't have.
    private Person createNewPerson(String phoneNumber)
            throws ServiceException, InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException {

        SoapBindingStub binding = SalesforceProxy.createBinding();

        Person__c[] person = new Person__c[1];
        person[0] = new Person__c();

        person[0].setFirst_Name__c(phoneNumber);
        person[0].setLast_Name__c(phoneNumber);
        person[0].setRaw_Mobile_Number__c(phoneNumber);

        SaveResult saveResult[] = binding.create(person);
        if (!saveResult[0].isSuccess()) {
            return null;
        }
        Person newPerson = new Person(phoneNumber, phoneNumber);
        newPerson.setSalesforceId(saveResult[0].getId());
        return newPerson;
    }

    // Add the link to the group that allows the person to subscribe to a topic
    private boolean addPersonGroupAssociations(Person person)
            throws InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException, ServiceException {

        SoapBindingStub binding = SalesforceProxy.createBinding();
        ArrayList<Person_Group_Association__c> existingSubscriptions = person.getSubscriptions();
        ArrayList<Person_Group_Association__c> associations = new ArrayList<Person_Group_Association__c>();
        for (String groupId : topicsToAdd.keySet()) {
            boolean addGroup = true;
            for (Person_Group_Association__c existingSubscription: existingSubscriptions) {
                if (existingSubscription.getGroup__c().equalsIgnoreCase(groupId)) {
                    this.groupsNotChanged.add(groupId);
                    addGroup = false;
                    break;
                }
            }
            if (addGroup) {
                Person_Group_Association__c newAssociation = new Person_Group_Association__c();
                newAssociation.setPerson__c(person.getSalesforceId());
                newAssociation.setGroup__c(groupId);
                associations.add(newAssociation);
                this.groupsToChange.add(groupId);
            }
        }

        if (this.groupsToChange.size() == 0 && this.groupsNotChanged.size() == 0) {
            return false;
        }

        if (this.groupsToChange.size() > 0) {
            SaveResult[] saveResult = binding.create(associations.toArray(new Person_Group_Association__c[0]));
            if (saveResult.length == 0) {
                return false;
            }
        }
        return true;
    }
    
    private boolean removePersonGroupAssociation(Person person)
            throws InvalidIdFault, UnexpectedErrorFault, LoginFault, RemoteException, ServiceException {

        SoapBindingStub binding = SalesforceProxy.createBinding();
        ArrayList<Person_Group_Association__c> existingSubscriptions = person.getSubscriptions();
        ArrayList<String> personGroupAssociationIds = new ArrayList<String>();


        for (String groupId : topicsToAdd.keySet()) {
            boolean addGroup = false;
            for (Person_Group_Association__c existingSubscription: existingSubscriptions) {
                if (existingSubscription.getGroup__c().equalsIgnoreCase(groupId)) {
                    this.groupsToChange.add(groupId);
                    personGroupAssociationIds.add(existingSubscription.getId());
                    addGroup = true;
                    break;
                }
            }
            if (!addGroup) {
                this.groupsNotChanged.add(groupId);
            }
        }
        if (this.groupsToChange.size() == 0 && this.groupsNotChanged.size() == 0) {
            return false;
        }
        DeleteResult deleteResult[] = binding.delete(personGroupAssociationIds.toArray(new String[0]));
        if (this.groupsToChange.size() > 0) {
            if (deleteResult.length == 0) {
                return false;
            }
        }
        return true;
    }

    private String generateQueryParams(String[] params) {

        StringBuilder queryParam = new StringBuilder();
        for (int i = 0; i < params.length; i++) {
            queryParam.append("'");
            queryParam.append(params[i]);
            queryParam.append("'");
            
            if (i != params.length - 1) {
                queryParam.append(", ");
            }
        }
        return queryParam.toString();
    }

    private boolean checkInputIsNumber(String checkString) {
        try {
            Integer.parseInt(checkString);
        }
        catch (NumberFormatException e) {
            return false;
        }
        return true;
    }
    
    private String parseSuccessMessage() {
        StringBuilder message = new StringBuilder();
        
        // Check that we have actually subscribed the user to anything.
        if (this.groupsToChange.size() > 0) {
            if (this.subscribe){
                message.append(this.SUCCESS_HEADER_SUBSCRIBE_STRING);
            }
            else {
                message.append(this.SUCCESS_HEADER_UNSUBSCRIBE_STRING);
            }
            message.append(this.NEW_LINE_STRING);
            for (String groupId : this.groupsToChange) {
                message.append(this.topicsToAdd.get(groupId).getName());
                message.append(this.NEW_LINE_STRING);
            }
        }
        
        // Check to see if there were any topics already subscribed to.
        if (this.groupsNotChanged.size() > 0) {
            if (this.subscribe){
                message.append(this.FAIL_HEADER_SUBSCRIBE_STRING);
            }
            else {
                message.append(this.FAIL_HEADER_UNSUBSCRIBE_STRING);
            }
            message.append(this.NEW_LINE_STRING);
            for (String groupId : this.groupsNotChanged) {
                message.append(this.topicsToAdd.get(groupId).getName());
                message.append(this.NEW_LINE_STRING);
            }
        }
        return message.toString();
    }
}
