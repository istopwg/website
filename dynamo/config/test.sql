--
-- "test.sql"
--
-- Test data for development of pwg.org server.  This MUST NOT be applied to
-- the production server (www.pwg.org).
--
-- Apply the test data on top of pwg.sql, e.g.:
--
--   mysql pwg <pwg.sql
--   mysql pwg <selfcert.sql
--   mysql pwg <test.sql
--


-- These are all test accounts with the password "Printing123".
-- (Not present on the production servers...)
INSERT INTO user VALUES(1, 2, 'webmaster@pwg.org','PWG Webmaster',1,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',1,1,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(2, 2, 'wwwtestuser@pwg.org','PWG Test User',1,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(3, 2, 'wwwtesteditor@pwg.org','PWG Test Editor',1,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,1,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(4, 2, 'wwwtestmember@pwg.org','PWG Test Member',100,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);

-- Organizations for test users
INSERT INTO organization VALUES(100,2,'Test Member Company 1','','2014-07-20',1,'2014-07-20',1);


-- IPP Everywhere Printers
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 114','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,0,0,0,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 214','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,0,0,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 314','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',0,1,0,1,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 414','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,0,1,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 514','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',0,1,0,1,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 614','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,1,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 714','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,1,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 814','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,1,'2014-08-30 12:00:00',4);
INSERT INTO printer VALUES(NULL,100,'TruePrint','Example TruePrint 914','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,1,'2014-08-30 12:00:00',4);

