--
-- "pwg.sql"
--
-- Database schema for the PWG web pages.
--


--
-- Site Features:
--
--   - Users
--   - News/announcements
--   - Issues
--   - Certified printers
--   - Pending certifications (submissions)
--   - Comments (attached to pretty much anything, although initially just for
--     issues and certification stuff)
--


--
-- Schema for table 'user'
--
-- This table lists the registered users for the site.  Various pages use
-- this table when doing login/logout stuff and when listing the available
-- users to assign stuff to.
--

DROP TABLE IF EXISTS user;
CREATE TABLE user (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  status INTEGER DEFAULT 1,		-- 0 = banned, 1 = pending, 2 = enabled, 3 = deleted
  email VARCHAR(255) UNIQUE NOT NULL,	-- Email address
  name VARCHAR(255) NOT NULL,		-- Real name
  organization_id INTEGER NOT NULL,	-- Organization
  hash CHAR(128) NOT NULL,		-- crypt(password,sha512salt)
  is_admin BOOLEAN DEFAULT FALSE,	-- FALSE/0 = user, TRUE/1 = admin
  is_editor BOOLEAN DEFAULT FALSE,	-- FALSE/0 = not editor, TRUE/1 = editor
  is_member BOOLEAN DEFAULT FALSE,	-- FALSE/0 = not PWG member, TRUE/1 = PWG member
  is_reviewer BOOLEAN DEFAULT FALSE,	-- FALSE/0 = cannot review, TRUE/1 = can review
  is_submitter BOOLEAN DEFAULT FALSE,	-- FALSE/0 = cannot submit, TRUE/1 = can submit
  timezone VARCHAR(255) NOT NULL,	-- Timezone for user
  itemsperpage INTEGER DEFAULT 10 NOT NULL,
					-- Default items per page
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER,			-- User that created the user
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER,			-- User that made the last change

  INDEX(organization_id),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- These are all test accounts with the password "Printing123".
-- (Not present on the production servers...)
INSERT INTO user VALUES(1, 2, 'webmaster@pwg.org','PWG Webmaster',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',1,1,1,1,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(2, 2, 'wwwtestuser@pwg.org','PWG Test User',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(3, 2, 'wwwtesteditor@pwg.org','PWG Test Editor',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,1,0,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(4, 2, 'wwwtestmember@pwg.org','PWG Test Member',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,1,0,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(5, 2, 'wwwtestreview@pwg.org','PWG Test Reviewer',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,1,0,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);
INSERT INTO user VALUES(6, 2, 'wwwtestsubmit@pwg.org','PWG Test Submitter',0,'$6$68e043b431d79cce$7aa1mK7RxdK15B3XcBU9grtBqaTc9Nypym8IV2JWB6yHEuX5s.N3mMVjJ9udCprIPqslwa/V0vdBL/SOaXxqi1',0,0,0,0,1,'America/Toronto',50,'2014-07-20 12:00:00',1,'2014-07-20 12:00:00',1);


--
-- Schema for table 'organization'
--
-- This table tracks organizations associated with users and certifications.
--

DROP TABLE IF EXISTS manufacturer;
DROP TABLE IF EXISTS organization;
CREATE TABLE organization (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Manufacturer ID
  status INTEGER NOT NULL,		-- 0 = non-member, 1 = non-voting member, 2 = small voting member, 3 = large voting member
  name VARCHAR(255) NOT NULL,		-- Organization name
  domain VARCHAR(255) NOT NULL,		-- Domain name
  is_everywhere BOOLEAN NOT NULL,	-- FALSE/0 = not IPP Everywhere, TRUE/1 = IPP Everywhere
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that submitted the printer
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO organization VALUES(1,3,'Apple Inc.','apple.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(2,3,'Brother Industries Ltd','brother.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(3,3,'Canon Inc.','canon.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(4,2,'Conexant','conexant.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(5,2,'CSR','csr.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(6,3,'Dell','dell.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(7,2,'Digital Imaging Technology','itekus.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(8,3,'Epson','epson.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(9,2,'Fenestrae','udocx.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(10,3,'Fuji Xerox Co Ltd','fujixerox.co.jp',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(11,3,'Hewlett Packard Company','hp.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(12,2,'High North Inc.','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(13,3,'Konica Minolta','konicaminolta.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(14,3,'Kyocera Document Solutions Inc.','kyocera.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(15,3,'Lexmark','lexmark.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(16,2,'Meteor Networks','meteornetworks.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(17,3,'Microsoft','microsoft.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(18,2,'MPI Tech','mpitech.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(19,2,'MWA Intelligence Inc.','mwaintelligence.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(20,2,'Northlake Software Inc.','nls.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(21,3,'Oki Data Americas Inc.','okidata.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(22,3,'Ricoh','ricoh.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(23,2,'Quality Logic Inc.','qualitylogic.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(24,3,'Samsung Electronics Corporation','samsung.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(25,3,'Sharp','sharp.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(26,1,'Technical Interface Consulting','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(27,2,'Thinxtream Technologies','thinxtream.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(28,2,'Tykodi Consulting Services LLC','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(29,3,'Toshiba','toshiba.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(30,3,'Xerox Corporation','xerox.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(31,2,'Individual: Daniel Brennan','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(32,1,'Individual: Daniel Dressler','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(33,2,'Individual: Nancy Chen','',0,'2014-07-20',1,'2014-07-20',1);


--
-- Schema for table 'workgroup'
--
-- This table lists the PWG workgroups
--

DROP TABLE IF EXISTS workgroup;
CREATE TABLE workgroup (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  status INTEGER NOT NULL,		-- 0 = inactive, 1 = active BOF, 2 = active workgroup
  name VARCHAR(255) NOT NULL,		-- Workgroup name
  wwwdir VARCHAR(255) NOT NULL,		-- Directory name on web server
  ftpdir VARCHAR(255) NOT NULL,		-- Directory name on FTP server
  list VARCHAR(255) NOT NULL,		-- Mailing list address
  contents TEXT NOT NULL,		-- Description of workgroup
  chair_id INTEGER,			-- Chair of WG
  vicechair_id INTEGER,			-- Vice/co chair of WG
  secretary_id INTEGER,			-- Secretary of WG
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER,			-- User that created the user
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER,			-- User that made the last change

  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO workgroup VALUES(1, 2, 'Printer Working Group', 'chair', 'general', 'chair@pwg.org', '', 0, 0, 0, '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(2, 2, 'Internet Printing Protocol (IPP)', 'ipp', 'ipp', 'ipp@pwg.org', '', 0, 0, 0, '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(3, 2, 'Semantic Model (SM)', 'sm', 'sm3', 'sm3@pwg.org', '', 0, 0, 0, '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(4, 2, 'Imaging Device Security (IDS)', 'ids', 'ids', 'ids@pwg.org', '', 0, 0, 0, '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(5, 2, 'Cloud Imaging Model', 'cloud', 'cloud', 'cloud@pwg.org', '', 0, 0, 0, '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(6, 0, 'Workgroup for Imaging Management Solutions (WIMS)', 'wims', 'wims', 'wims@pwg.org', '', 0, 0, 0, '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);


--
-- Schema for table 'article'
--
-- This table tracks news announcements and other (brief) articles.
--

DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS article;
CREATE TABLE article (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Article ID
  workgroup_id INTEGER NOT NULL,	-- Organization ID or 0 for PWG-wide
  title VARCHAR(255) NOT NULL,		-- Title
  contents TEXT NOT NULL,		-- Text message
  url VARCHAR(255) NOT NULL,		-- URL to additional content
  display_until VARCHAR(255) NOT NULL,	-- Expiration date, if any
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that posted the article
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(workgroup_id),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO article VALUES(NULL,4,'PWG Approved: PWG Hardcopy Device Health Assessment Attributes','PWG Candidate Standard 5110.1-2013: PWG Hardcopy Device Health Assessment Attributes defines a set of attributes for Hardcopy Devices (HCDs) that may be used in the various network health assessment protocols to measure the fitness of a HCD to attach to the network.','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20130401-5110.1.pdf','','2013-05-01 12:00:00',1,'2013-05-01 12:00:00',1);
INSERT INTO article VALUES(NULL,4,'PWG Approved: PWG Hardcopy Device Health Assessment Network Access Protection Protocol Binding (HCD-NAP)','PWG Candidate Standard 5110.2-2013: PWG Hardcopy Device Health Assessment Network Access Protection Protocol Binding (HCD-NAP) defines the application of security policy enforcement mechanisms to imaging devices. This document specifies how the Microsoft Network Access Protection (NAP) protocol is to be used, along with the set of health assessment attributes created especially for HCDs, to allow access to such devices based upon the locally defined security policy.','http://ftp.pwg.org/pub/pwg/candidates/cs-ids-napsoh10-20130401-5110.2.pdf','','2013-05-01 12:00:00',1,'2013-05-01 12:00:00',1);
INSERT INTO article VALUES(NULL,4,'PWG Approved: PWG Common Log Format (PWG-LOG)','PWG Candidate Standard 5110.3-2013: PWG Common Log Format (PWG-LOG) defines a common log format for hardcopy device events that can be used with existing logging protocols such as SYSLOG. While the focus of this format is on security and auditing of devices, it also supports logging of arbitrary events such as those defined by the IPP Event Notifications and Subscriptions (RFC 3995) specification.','http://ftp.pwg.org/pub/pwg/candidates/cs-ids-log10-20130401.5110.3.pdf','','2013-05-01 12:00:00',1,'2013-05-01 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Last Call of IPP FaxOut Service','The PWG Last Call of the IPP FaxOut Service has begun. The IPP FaxOut Service specification defines an IPP extension to support the PWG Semantic Model FaxOut service over IPP. Please provide your responses prior to June 7th so that we can advance it to a formal vote.','http://www.pwg.org/archives/pwg-announce/2013/003108.html','2013-06-07','2013-05-01 12:00:00',1,'2013-06-07 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'Printing from Mobile Devices','According to a recent [[http://www.forbes.com/sites/ralphjennings/2013/04/10/smartphones-tablets-tab-phones-edging-pcs-off-the-shelf/|Forbes blog]] on the growing use of smartphones and tablets, "Shipments of smartphones and tablet PCs are both on the rise, with the first up 40 percent and the second nearly 100 percent this year, market research firm TrendForce forecasts." What does that mean for printing? Does everyone with a smartphone or tablet want to give up the option to print something from the device?','blog/printing-from-mobile-devices.html','','2013-05-12 12:00:00',1,'2013-05-12 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Joint PWG-OpenPrinting Face-to-Face Meeting','The joint PWG-OpenPrinting meeting is being hosted by Apple at their facilities in Cupertino, CA from May 14-17. Topics include: printing on Linux and other platforms, Cloud-based imaging services, IPP Shared Infrastructure Extensions, IPP Everywhere self-certification, IPP Best Practices, the Transform service, mapping of job ticket formats, and printer-related security.','chair/meeting-info/may_2013_cupertino.html','','2013-05-14 12:00:00',1,'2013-05-14 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'New IPP Everywhere Standard Lets Users Print From Smartphones and Tablets Without Apps or Vendor-Specific Device Drivers','CUPERTINO, Calif., May 14, 2013 – Users of today’s smartphones and tablets can do almost anything on these devices – except print from them natively, without downloading apps or vendor-specific device drivers. Now, an industry standards group, the IEEE-ISTO Printer Working Group, has solved that problem with a new specification, IPP Everywhere.','blog/ipp-everywhere-press-release.html','','2013-05-14 12:00:00',1,'2013-05-14 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Printing from Mobile Devices, Part 2','In a [[blog/printing-from-mobile-devices.html|previous post]], we talked about how printing from mobile devices has moved from a non-starter to a somewhat more complex capability based around vendor-specific apps and with little standardization. Wondering if mobile device users really do want to print something?','blog/printing-from-mobile-devices-2.html','','2013-05-17 12:00:00',1,'2013-05-17 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Printing from Mobile Devices, Part 3','The PWG has announced IPP Everywhere to enable printing from mobile devices without apps or vendor-specific drivers. In our [[blog/printing-from-mobile-devices-2.html|previous post]] we talked about the advantages of allowing native printing capabilities. Users don''t have to download apps or update them. They don''t have to create accounts or sign in. Touch "print", choose options, and you''re good to go.','blog/printing-from-mobile-devices-3.html','','2013-05-20 12:00:00',1,'2013-05-20 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Last Call of IPP Transaction-Based Printing Extensions (concluded)','The PWG Last Call of the IPP Transaction-Based Printing Extensions has begun. The IPP Transaction-Based Printing Extensions specification defines an IPP extension to support monetary, quota-based, and release printing transactions. Please provide your responses prior to August 30th so that we can advance it to a formal vote.','http://www.pwg.org/archives/pwg-announce/2013/003567.html','','2013-07-30 12:00:00',1,'2013-08-30 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Summary of PWG Face-to-Face Meeting in Camas, WA','The Printer Working Group recently held a face-to-face meeting on August 6-8, 2013 at Sharp''s facilities in Camas, WA. We discussed a potential 3D printing BOF, reviewed several drafts of in-progress specifications, and set new goals and milestones for the Cloud Imaging, Internet Printing Protocol, and Semantic Model workgroups.','blog/august-2013-summary.html','2013-08-08','2013-08-14 12:00:00',1,'2013-08-14 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'PWG Meets with Broadband Forum','This week in Atlanta, a representative from the IEEE-ISTO Printer Working Group (PWG) presented and participated in the Broadband Forum''s Q3 meeting to discuss wide-area Internet-based management of printers by telecom providers and how PWG standards can support that effort.','blog/pwg-meets-with-broadband-forum.html','','2013-09-20',1,'2013-09-20',1);
INSERT INTO article VALUES(NULL,1,'W3C Workshop on Publishing and the Open Web Platform','A representative from the IEEE-ISTO Printer Working Group (PWG) attended the World Wide Web Consortium (W3C) [[http://www.w3.org/2012/12/global-publisher/Overview.html|"Publishing and the Open Web Platform"]] workshop in September 2013 and participated in the "Standards Bodies: Who does what?" panel.','blog/w3c-workshop-september-2013.html','','2013-10-08 12:00:00',1,'2013-10-08 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Formal Vote of IPP Transaction-Based Printing Extensions','The PWG Formal Vote of the IPP Transaction-Base Printing Extensions has begun. The IPP Transaction-Based Printing Extensions specification defines extensions to the Internet Printing Protocol that support the business transaction logic needed for paid, PIN, release, and quota-based printing through local and commercial services.','http://www.pwg.org/archives/pwg-announce/2013/003579.html','2013-11-08','2013-10-08 12:00:00',1,'2013-11-08 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Formal Vote of IPP FaxOut Service','The PWG Formal Vote of the IPP FaxOut Service has begun. The IPP FaxOut specification defines defines an IPP extension to support the PWG Semantic Model FaxOut service over IPP.','http://www.pwg.org/archives/pwg-announce/2013/003581.html','2013-11-15','2013-10-10 12:00:00',1,'2013-11-15 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Summary of PWG Face-to-Face Meeting in Cupertino, CA','The Printer Working Group recently held a face-to-face meeting on October 22-24, 2013 at Ricoh''s facilities in Cupertino, CA. We discussed current and future liaisons with other standards groups, reviewed several drafts of in-progress. specifications, and outlined future IPP System Control Service and Semantic Model 3.0 documents.','blog/october-2013-summary.html','','2013-10-28 12:00:00',1,'2013-10-28 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Approved: IPP Transaction-Based Printing Extensions','PWG Candidate Standard 5100.16-2013: IPP Transaction-Based Printing Extensions has been published.','http://ftp.pwg.org/pub/pwg/candidates/cs-ipptrans10-20131108-5100.16.pdf','','2013-11-22 12:00:00',1,'2013-11-22 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Approved: IPP FaxOut Service','PWG Candidate Standard 5100.15-2013: IPP FaxOut Service has been published.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfaxout10-20131115-5100.15.pdf','','2013-12-02',1,'2013-12-02',1);
INSERT INTO article VALUES(NULL,4,'PWG Approved: Update to PWG Hardcopy Device Health Assessment Attributes','An update to PWG Candidate Standards 5110.1: PWG Hardcopy Device Health Assessment Attributes has been published.','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20140106-5110.1.pdf','','2014-01-06 12:00:00',1,'2014-01-06 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Summary of PWG Face-to-Face Meeting in Irvine, CA','The Printer Working Group recently held a face-to-face meeting on February 4-6, 2014 at Samsung''s facilities in Irvine, CA. We discussed current and future liaisons with other standards groups, reviewed several drafts of in-progress. specifications, and enjoyed presentations from CIP4 and the Mopria Alliance.','blog/february-2014-summary.html','','2014-02-12 12:00:00',1,'2014-02-12 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Summary of PWG Face-to-Face Meeting in Cupertino, CA','The Printer Working Group recently held a face-to-face meeting on May 13-15, 2014 at Apple''s facilities in Cupertino, CA. We discussed current and future liaisons with other standards groups, reviewed several drafts of in-progress. specifications, and developed the outline of the three new Semantic Model 3.0 specifications.','blog/may-2014-summary.html','','2014-05-22 12:00:00',1,'2014-05-22 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Last Call: IPP Scan Service','The PWG Last Call of the IPP Scan Service has begun. The IPP Scan Service specification defines an IPP extension to support the PWG Semantic Model Scan service over IPP.','http://www.pwg.org/archives/pwg-announce/2014/003608.html','2014-07-21','2014-06-20 12:00:00',1,'2014-07-21 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'PWG Approved: Update to IPP FaxOut Service','PWG Candidate Standard 5100.15: IPP FaxOut Service has been updated.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfaxout10-20140618-5100.15.pdf','','2014-07-14 12:00:00',1,'2014-07-14 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Interview with Paul Tykodi','Watch Paul Tykodi, co-chair of the Internet Printing Protocol workgroup and vice chair of the Semantic Model workgroup, talk about the PWG and its standards work with Cary Sherburne of WhatTheyThink.','http://whattheythink.com/video/69439-paul-tykodi-printer-working-group-standards/','','2014-07-15 12:00:00',1,'2014-07-15 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'August 2014 Face-to-Face Meeting','The joint PWG-OpenPrinting meeting will be held on August 12-15, 2014 at the Hilton Garden Inn in Toronto/Mississauga. Please register to attend before August 1, 2014.','chair/meeting-info/august_2014_toronto.html','2014-08-15','2014-06-25 20:08:17',1,'2014-06-25 20:08:17',1);
INSERT INTO article VALUES(NULL,2,'PWG Last Call: IPP Finishings 2.0','The PWG Last Call of IPP Finishings 2.0 (FIN2) has begun. This specification defines new "finishings" and "finishings-col" Job Template attribute values to specify additional finishing intent, including the placement of finishings with respect to the corners and edges of portrait and landscape documents.','http://www.pwg.org/archives/pwg-announce/2014/003612.html','2014-08-22','2014-07-28 12:00:00',1,'2014-07-28 12:00:00',1);


--
-- Schema for table 'document'
--
-- This table stores approved documents, white papers, working drafts, and minutes.
--

DROP TABLE IF EXISTS document;
CREATE TABLE document (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  replaces_id INTEGER,			-- Document ID this replaces, if any
  workgroup_id INTEGER,			-- Workgroup, if any
  status INTEGER,			-- 0 = withdrawn, 1 = initial working draft,
					-- 2 = interim working draft,
					-- 3 = prototype working draft,
					-- 4 = stable working draft,
					-- 5 = conference call minutes,
					-- 6 = face-to-face minutes,
					-- 7 = (published) white paper,
					-- 8 = (approved) charter,
					-- 9 = (published) informational,
					-- 10 = candidate standard,
					-- 11 = (full) standard
  number VARCHAR(255) NOT NULL,		-- PWG document number, if any (5100.1, etc.)
  version VARCHAR(255) NOT NULL,	-- Document version number, if any (1.0, etc.)
  title VARCHAR(255) NOT NULL,		-- Title of document
  contents TEXT NOT NULL,		-- Abstract of document
  editable_url VARCHAR(255) NOT NULL,	-- Published URL of editable (Word) file
  clean_url VARCHAR(255) NOT NULL,	-- Published URL of read-only (PDF) file w/o change marks
  redline_url VARCHAR(255) NOT NULL,	-- Published URL of read-only (PDF) file w/change marks
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that created the STR
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(workgroup_id),
  INDEX(status),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Schema for table 'issue'
--
-- This table stores issue reports.
--

DROP TABLE IF EXISTS issue;
CREATE TABLE issue (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  parent_id INTEGER,			-- "Duplicate of" number
  workgroup_id INTEGER,			-- Workgroup, if any
  document_id INTEGER,			-- Document ID, if any
  status INTEGER,			-- 1 = new/unconfirmed, 2 = pending,
					-- 3 = active, 4 = closed/resolved,
					-- 5 = closed/unresolved
  priority INTEGER,			-- 0 = unassigned, 1 = critical, 2 = high,
					-- 3 = moderate, 4 = low, 5 = enhancement
  title VARCHAR(255) NOT NULL,		-- Plain text summary
  assigned_id INTEGER NOT NULL,		-- User that is working the issue
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that created the STR
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(parent_id),
  INDEX(workgroup_id),
  INDEX(document_id),
  INDEX(assigned_id),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Schema for table 'comment'
--
-- This table tracks the comments that are attached to articles, issues,
-- printers, submissions, and users.
--

DROP TABLE IF EXISTS comment;
CREATE TABLE comment (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Comment ID
  ref_id VARCHAR(255),			-- Reference ("table_id")
  contents TEXT NOT NULL,		-- Text message
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that posted the text
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(ref_id),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Schema for table 'submission'
--
-- This table tracks the submitted IPP Everywhere printers.
--

DROP TABLE IF EXISTS submission;
CREATE TABLE submission (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Submission ID
  organization_id INTEGER,		-- Organization ID
  contact_name VARCHAR(255) NOT NULL,	-- Person to contact
  contact_email VARCHAR(255) NOT NULL,	-- That person's email
  product_family VARCHAR(255) NOT NULL,	-- Product family
  models TEXT NOT NULL,			-- Model names, one per line
  url VARCHAR(255) NOT NULL,		-- Product/organization URL
  cert_version VARCHAR(255) NOT NULL,	-- Certification version (M.m - YYYY-MM-DD)
  used_approved BOOLEAN DEFAULT FALSE,	-- Used approved software?
  used_prodready BOOLEAN DEFAULT FALSE,	-- Used production-ready firmware? */
  printed_correctly BOOLEAN DEFAULT FALSE,
					-- Documents printed correctly?
  reviewer1_id INTEGER NOT NULL,	-- First reviewer
  reviewer1_status INTEGER NOT NULL,	-- 0 = pending, 1 = SC review,
					-- 2 = approved, 3 = rejected
  reviewer2_id INTEGER NOT NULL,	-- Second reviewer
  reviewer2_status INTEGER NOT NULL,	-- 0 = pending, 1 = SC review,
					-- 2 = approved, 3 = rejected
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that submitted the printer
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(reviewer1_id),
  INDEX(reviewer2_id),
  INDEX(cert_version),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Schema for table 'exception'
--
-- This table tracks the submitted exception requests for IPP Everywhere printers.
--

DROP TABLE IF EXISTS exception;
CREATE TABLE exception (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Printer ID
  submission_id INTEGER NOT NULL,	-- Submission ID
  item VARCHAR(255) NOT NULL,		-- Test item (B, I, or D followed by test item)
  contents TEXT NOT NULL,		-- Text explaining why an exception is being requested
  reviewer1_status INTEGER NOT NULL,	-- 0 = pending, 1 = SC review,
					-- 2 = approved, 3 = rejected
  reviewer2_status INTEGER NOT NULL,	-- 0 = pending, 1 = SC review,
					-- 2 = approved, 3 = rejected
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that submitted the printer
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Schema for table 'printer'
--
-- This table tracks the approved IPP Everywhere printers.
--

DROP TABLE IF EXISTS printer;
CREATE TABLE printer (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Printer ID
  submission_id INTEGER NOT NULL,	-- Submission ID for this entry
  organization_id INTEGER,		-- Organization ID
  product_family VARCHAR(255) NOT NULL,	-- Product family
  model VARCHAR(255) NOT NULL,		-- Model name
  url VARCHAR(255) NOT NULL,		-- Product/organization URL
  cert_version VARCHAR(255) NOT NULL,	-- Certification version (M.m - YYYY-MM-DD)
  color_supported BOOLEAN DEFAULT FALSE,-- FALSE/0 = B&W, TRUE/1 = color
  duplex_supported BOOLEAN DEFAULT FALSE,
					-- FALSE/0 = simplex, TRUE/1 = duplex
  finishings_supported BOOLEAN DEFAULT FALSE,
					-- FALSE/0 = no finishers, TRUE/1 = has finishers
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that submitted the printer
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(submission_id),
  INDEX(organization_id),
  INDEX(product_family),
  INDEX(cert_version),
  INDEX(color_supported),
  INDEX(duplex_supported),
  INDEX(finishings_supported),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- End of "pwg.sql".
--
