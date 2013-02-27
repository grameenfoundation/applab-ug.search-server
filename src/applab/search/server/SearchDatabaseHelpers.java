/**
 *
 * Copyright (c) 2013 AppLab, Grameen Foundation
 *
 **/

package applab.search.server;

import applab.server.DatabaseHelpers;
import applab.server.WebAppId;
import java.sql.Connection;
import java.sql.SQLException;

public class SearchDatabaseHelpers {
    static final String JDBC_DRIVER = "com.mysql.jdbc.Driver";
    static Connection readerConnection;
    static Connection writerConnection;
    static Connection searchReaderConnection;
    static Connection searchWriterConnection;

    public static Connection getReaderConnection() throws ClassNotFoundException, SQLException {
        if ((readerConnection == null) || (readerConnection.isClosed())) {
            readerConnection = DatabaseHelpers.createReaderConnection(WebAppId.search);
        }
        return readerConnection;
    }

    public static Connection getWriterConnection() throws ClassNotFoundException, SQLException {
        if ((writerConnection == null) || (writerConnection.isClosed())) {
            writerConnection = DatabaseHelpers.createConnection(WebAppId.search);
        }
        return writerConnection;
    }
}