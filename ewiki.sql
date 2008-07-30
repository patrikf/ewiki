CREATE TABLE
    ewiki_users
    (
        "user" VARCHAR NOT NULL PRIMARY KEY,
        "password" VARCHAR NOT NULL,
        "name" VARCHAR NOT NULL UNIQUE,
        "email" VARCHAR NOT NULL UNIQUE,
        "session" VARCHAR NULL DEFAULT NULL UNIQUE
    );
