--
-- "test.sql"
--
-- Test data for development of pwg.org server.  This MUST NOT be applied to
-- the production server (www.pwg.org).
--
-- Apply the test data on top of pwg.sql, e.g.:
--
--   mysql pwg <pwg.sql
--   mysql pwg <test.sql
--
-- Then copy the plist files (also in this directory) to the submission
-- directory as follows:
--
--   mkdir /path/to/submission/dir/1
--   cp good-bonjour.plist /path/to/submission/dir/1/bonjour.plist
--   cp good-document.plist /path/to/submission/dir/1/document.plist
--   cp good-ipp.plist /path/to/submission/dir/1/ipp.plist
--   mkdir /path/to/submission/dir/2
--   cp bad-bonjour.plist /path/to/submission/dir/2/bonjour.plist
--   cp bad-document.plist /path/to/submission/dir/2/document.plist
--   cp bad-ipp.plist /path/to/submission/dir/2/ipp.plist
--


-- These are all test accounts with the password "Printing123".
-- (Not present on the production servers...)
INSERT INTO user VALUES(1, 2, 'webmaster@pwg.org','PWG Webmaster',1,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',1,1,1,1,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(2, 2, 'wwwtestuser@pwg.org','PWG Test User',1,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(3, 2, 'wwwtesteditor@pwg.org','PWG Test Editor',1,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,1,0,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(4, 2, 'wwwtestmember@pwg.org','PWG Test Member',1,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,1,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(5, 2, 'wwwtestreview1@pwg.org','PWG Test Reviewer 1',100,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,1,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(6, 2, 'wwwtestreview2@pwg.org','PWG Test Reviewer 2',101,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,1,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(7, 2, 'wwwtestsubmit@pwg.org','PWG Test Submitter',102,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,0,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);

-- Organizations for test users
INSERT INTO organization VALUES(100,2,'Test Review Company 1','',1,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(101,2,'Test Review Company 2','',1,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(102,2,'Test Submit Company 1','',1,'2014-07-20',1,'2014-07-20',1);

-- Submissions, comments, and printers
INSERT INTO submission VALUES(1,2,102,'PWG Test Submitter','wwwtestsubmit@pwg.org','TruePrint','Example TruePrint 114
Example TruePrint 214
Example TruePrint 314
Example TruePrint 414
Example TruePrint 514
Example TruePrint 614
Example TruePrint 714
Example TruePrint 814
Example TruePrint 914','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,'',6,2,7,2,'2014-08-26 12:00:00',7,'2014-08-30 12:00:00',6);
INSERT INTO comment VALUES(NULL,'submission_1','Looks good, not issues found in submission.','2014-08-29 12:00:00',5,'2014-08-29 12:00:00',5);
INSERT INTO comment VALUES(NULL,'submission_1','I agree, good submission, approved!','2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 114','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,0,0,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 214','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,0,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 314','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',0,1,0,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 414','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,0,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 514','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',0,1,0,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 614','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 714','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 814','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);
INSERT INTO printer VALUES(NULL,1,102,'TruePrint','Example TruePrint 914','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,'2014-08-30 12:00:00',6,'2014-08-30 12:00:00',6);

INSERT INTO submission VALUES(2,5,102,'PWG Test Submitter','wwwtestsubmit@pwg.org','FalsePrint','Example FalsePrint 114
Example FalsePrint 214
Example FalsePrint 314
Example FalsePrint 414
Example FalsePrint 514
Example FalsePrint 614
Example FalsePrint 714
Example FalsePrint 814
Example FalsePrint 914','http://www.cups.org/','org.pwg.ipp-everywhere.20140826',1,1,1,'',5,3,6,3,'2014-08-31 12:00:00',7,'2014-09-11 12:00:00',6);
INSERT INTO comment VALUES(NULL,'submission_2','Major problems with this; not using the released tools, and the Bonjour tests show IPPS issues.','2014-09-04 12:00:00',5,'2014-09-04 12:00:00',5);
INSERT INTO comment VALUES(NULL,'submission_2','Also the PDF scaling tests all failed. That is a major interoperability issue for clients printing a mix of US Letter and A4 documents. Sorry, but I have to reject.','2014-09-05 12:00:00',6,'2014-09-05 12:00:00',6);
INSERT INTO comment VALUES(NULL,'submission_2','We''d like to appeal the rejection. Our customers don''t print on US Letter or A4.','2014-09-06 12:00:00',7,'2014-09-06 12:00:00',7);
INSERT INTO comment VALUES(NULL,'submission_2','The steering committee has reviewed your appeal and is upholding the reviewer''s judgement. If you report support for US Letter and A4, then your customers must be using it...','2014-09-11 12:00:00',6,'2014-09-11 12:00:00',6);
