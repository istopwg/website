--
-- "pwg.sql"
--
-- Database schema for the PWG web pages.
--


--
-- Site Features:
--
--   - Users (in pwg-user.sql now)
--   - News/announcements
--   - Issues
--   - Certified printers
--   - Pending certifications (submissions)
--   - Comments (attached to pretty much anything, although initially just for
--     issues and certification stuff)
--


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
INSERT INTO organization VALUES(1,3,'Printer Working Group','pwg.org',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(2,3,'Apple Inc.','apple.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(3,3,'Brother Industries Ltd','brother.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(4,3,'Canon Inc.','canon.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(5,2,'Conexant','conexant.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(6,2,'CSR','csr.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(7,3,'Dell','dell.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(8,2,'Digital Imaging Technology','itekus.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(9,3,'Epson','epson.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(10,2,'Fenestrae','udocx.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(11,3,'Fuji Xerox Co Ltd','fujixerox.co.jp',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(12,3,'Hewlett Packard Company','hp.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(13,2,'High North Inc.','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(14,3,'Konica Minolta','konicaminolta.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(15,3,'Kyocera Document Solutions Inc.','kyocera.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(16,3,'Lexmark','lexmark.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(17,2,'Meteor Networks','meteornetworks.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(18,3,'Microsoft','microsoft.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(19,2,'MPI Tech','mpitech.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(20,2,'MWA Intelligence Inc.','mwaintelligence.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(21,2,'Northlake Software Inc.','nls.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(22,3,'Oki Data Americas Inc.','okidata.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(23,3,'Ricoh','ricoh.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(24,2,'Quality Logic Inc.','qualitylogic.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(25,3,'Samsung Electronics Corporation','samsung.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(26,3,'Sharp','sharp.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(27,1,'Technical Interface Consulting','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(28,2,'Thinxtream Technologies','thinxtream.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(29,2,'Tykodi Consulting Services LLC','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(30,3,'Toshiba','toshiba.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(31,3,'Xerox Corporation','xerox.com',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(32,2,'Individual: Daniel Brennan','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(33,1,'Individual: Daniel Dressler','',0,'2014-07-20',1,'2014-07-20',1);
INSERT INTO organization VALUES(34,2,'Individual: Nancy Chen','',0,'2014-07-20',1,'2014-07-20',1);


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
INSERT INTO article VALUES(NULL,2,'PWG Last Call: IPP Finishings 2.0','The PWG Last Call of IPP Finishings 2.0 (FIN2) has begun. This specification defines new "finishings" and "finishings-col" Job Template attribute values to specify additional finishing intent, including the placement of finishings with respect to the corners and edges of portrait and landscape documents.','http://www.pwg.org/archives/pwg-announce/2014/003621.html','2014-09-122','2014-07-28 12:00:00',1,'2014-08-24 12:00:00',1);
INSERT INTO article VALUES(NULL,1,'Summary of PWG Face-to-Face Meeting in Toronto, ON','The Printer Working Group recently held a face-to-face meeting on August 12-15, 2014 in Toronto, Ontario. We discussed current and future liaison''s with other standards groups, discussed OpenPrinting work including a new implementation of IPP USB for Linux, reviewed several drafts of in-progress specifications, and held our first 3D Printing BOF.','blog/august-2014-summary.html','','2014-08-18 12:00:00',1,'2014-08-18 12:00:00',1);
INSERT INTO article VALUES(NULL,2,'IPP Everywhere Printer Self-Certifications Tools Now Available','The first public beta versions of the IPP Everywhere Printer Self-Certification tools are now available for Mac OS X 10.9 and later, Red Hat Enterprise Linux 7 and later, Ubuntu LTS 14.04 and later, and Windows 7 and later. Please contact [mailto:chair@pwg.org|Michael Sweet] if you would like to also test the submission portal on the new PWG web site.','ipp/everywhere.html','','2014-09-02 10:30:00',1,'2014-09-02 10:30:00',1);


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
  series INTEGER NOT NULL,		-- PWG document series (51xx, 0 if none)
  number INTEGER NOT NULL,		-- PWG document number in series (0 if none)
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

-- 5100.x
INSERT INTO document VALUES(1,0,2,10,5100,1,'1.0','IPP "finishings" attribute values extension','This document specifies the additional enum values ''fold'', ''trim'', ''bale'', ''booklet-maker'', ''jog-offset'', ''bind-left'', ''bind-top'', ''bind-right'', and ''bind-bottom'' for the IPP "finishings" Job Template attribute for use with the Internet Printing Protocol/1.0 (IPP) [RFC2566, RFC2565] and Internet Printing Protocol/1.1 (IPP) [RFC2911, RFC2910]. This attribute permits the client to specify additional finishing options, including values that include a specification of a coordinate system for the placement of finishings operation with respect to the corners and edges of portrait and landscape documents.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfinishings10-20010205-5100.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfinishings10-20010205-5100.1.pdf','','2001-02-05 12:00:00',1,'2001-02-05 12:00:00',1);

INSERT INTO document VALUES(2,0,2,10,5100,2,'1.0','IPP "output-bin" attribute extension','This document defines an extension to the Internet Printing Protocol/1.0 (IPP/1.0) [RFC2566, RFC2565] & IPP/1.1 [RFC2911, RFC2910] for the OPTIONAL "output-bin" (type2 keyword | name(MAX)) Job Template attribute. This attribute allows the client to specify in which output bin a job is to be placed and to query the Printer''s default and supported output bins.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippoutputbin10-20010207-5100.2.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippoutputbin10-20010207-5100.2.pdf','','2001-02-07 12:00:00',1,'2001-02-07 12:00:00',1);

INSERT INTO document VALUES(3,0,2,10,5100,3,'1.0','Production Printing Attributes - Set 1','This document specifies an extension to the Internet Printing Protocol/1.0 (IPP) [RFC2565, RFC2566] and IPP/1.1 [RFC2910, RFC2911]. This extension consists primarily of Job Template attributes defined for submitting print jobs primarily (but not limited to) to production printers. These attributes permit a user to control and/or override instructions in the document content to perform the following functions: print on document covers, control the positioning of stapling, force pages to the front side of the media, identify an imposition template, insert sheets into the document, provide an accounting id, provide an accounting user id, request accounting sheets, provide job sheet messages, request error sheets, provide a message to the operator, control the media used for job sheets, request media by characteristic (size, weight, etc.), request to check the media characteristics in an input tray, specify the presentation direction of page images with number-up, and shift the images of finished pages.
This extension also defines the "current-page-order" Job Description attribute, the "user-defined-values-supported" and "max-stitching-locations-supported" Printer Description attributes, and the ''resources-are-not-supported'' value for the "job-state-reasons" Job Description attribute. Some additional "media" keyword values are defined for use with the "media" and "media-col" Job Template attributes.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippprodprint10-20010212-5100.3.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippprodprint10-20010212-5100.3.pdf','','2001-02-12 12:00:00',1,'2001-02-12 12:00:00',1);

INSERT INTO document VALUES(4,0,2,0,5100,4,'1.0','IPP Override Attributes for Documents and Pages','This document specifies an extension to the Internet Printing Protocol/1.0 (IPP) [RFC2565, RFC2566] and IPP/1.1 [RFC2910, RFC2911]. This extension relaxes the restriction that each attribute value is the same for all pages, all documents and all document copies within a job. For example, with this extension, page 1 of a job could have a different media from the other pages in the job or document 2 of a job could be stapled while the other documents of the job are not. As another example, the first ten copies of a document could be printed on letter paper and stapled while the eleventh copy of the same document could be printed on transparencies with no staple.
This extension supports document overrides and page overrides by adding two new Job Template attributes: “document-overrides” and “page-overrides” -- both have a syntax type of “1setOf collection”. Each ‘collection’ value for “document-overrides” contains an attribute that identifies the overridden documents, namely “input-documents” or “output-documents”. The ‘collection’ value also contains one or more attributes that are overrides for the identified documents, e.g. “document-format”, “finishings”, and “media”. Each ‘collection’ value for “page-overrides” contains two attributes that identify the overridden pages, namely “input-documents” or “output-documents” plus “pages”. The ‘collection’ value also contains one or more attributes that are overrides for the identified pages, e.g. “sides” and “media”. When the overrides apply to some but not all document copies, the ‘collection’ value for “document-overrides” or “page-overrides” contains the attribute of “document-copies”.
This extension also supports subset finishing by adding a new Job Template attribute “pages-per-subset”, which specifies the number of pages per subset. The extension allows finishing and other document attributes to be applied to such subsets of pages.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippoverride10-20010207-5100.4.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippoverride10-20010207-5100.4.pdf','','2001-02-07 12:00:00',1,'2003-10-31 12:00:00',1);

INSERT INTO document VALUES(5,0,2,10,5100,5,'1.0','IPP Document Object','This IPP specification extends the IPP Protocol, Model and Semantics [rfc2910], [rfc2911] object model by defining a Document object. The [rfc2911] Job object is extended to contain one or more Document objects that are passive objects operated on by the Job. Multi-Document Jobs exist in IPP [rfc2911] but are not objects in their own right. This specification elevates the Document to an IPP object thus giving access to a document''s metadata within a Job. A Document object allows template attributes to be applied at both the Job and Document level. This enables document overrides of Job Template attributes.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippdocobject10-20031031-5100.5.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippdocobject10-20031031-5100.5.pdf','','2003-10-31 12:00:00',1,'2003-10-31 12:00:00',1);

INSERT INTO document VALUES(6,4,2,10,5100,6,'1.0','IPP Page Overrides','This IPP specification extends the IPP Model and Semantics [rfc2911] object model by relaxing the restriction that each attribute value is the same for all pages within a Document. For example, with this extension, page 1 of a job could have a different media than the other pages in the job.
This extension supports page Overrides by adding a new Job Template attribute: "overrides". Each ''collection'' value contains attributes that identify the attributes to Override and their associated values as well as the range of pages for the Override. The range of pages is specified by the "document-copies" attribute and the "pages" attribute to allow Overrides of pages in specific copies of the document.','http://ftp.pwg.org/pub/pwg/candidates/cs-ipppageoverride10-20031031-5100.6.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ipppageoverride10-20031031-5100.6.pdf','','2003-10-31 12:00:00',1,'2003-10-31 12:00:00',1);

INSERT INTO document VALUES(7,0,2,10,5100,7,'1.0','IPP Job Extensions','This IPP specification extends the Job semantics of the IPP Model and Semantics [rfc2911] object model. This specification defines some new Operation attributes for use in Job Creation and Document Creation operations. The Printer copies these Operation attributes to the corresponding Job Description attributes, which the clients may query. The Document Creation Operation attributes describe the Document Content and permit the Printer to reject requests that it cannot process correctly. Some corresponding "xxx-default" and "xxx-supported" Printer attributes are defined. This specification defines some Job Template attributes that apply to a multi-document Job as a whole and the "output-device" Job Template attribute that can apply to Documents and to Sheets as well as Jobs. This specification also defines some additional values for the "job-state-reasons" Job Description attribute. Each of the attributes defined in this specification are independent of each other and are OPTIONAL for a Printer to support.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippjobext10-20031031-5100.7.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippjobext10-20031031-5100.7.pdf','','2003-10-31 12:00:00',1,'2003-10-31 12:00:00',1);

INSERT INTO document VALUES(8,0,2,10,5100,8,'1.0','IPP "-actuals" Attributes','This document defines an extension to the Internet Printing Protocol (IPP) (RFC2911, RFC2910) for the OPTIONAL "-actual" set of Job Description attributes that correspond to Job Template attributes defined in IPP. These "-actual" attributes allow the client to determine the true results of a print job regardless of what was specified in the Create-Job or Print-Job operation.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippactuals10-20030313-5100.8.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippactuals10-20030313-5100.8.pdf','','2003-03-13 12:00:00',1,'2003-03-13 12:00:00',1);

INSERT INTO document VALUES(9,0,2,10,5100,9,'1.0','IPP Printer State Extensions v1.0','This document defines the new IPP Printer attributes "printer-alert" and "printer-alert-description" plus extensions to the IPP Printer attribute "printer-state-reasons" (defined in RFC 2911) and to the IANA Printer MIB textual convention "PrtAlertCodeTC" (originally published in RFC 3805) as follows:
(a) A standard encoding of all of the machine-readable columnar objects in the ''prtAlertTable'' defined in IETF Printer MIB v2 (RFC 3805) into substrings of values of the new IPP Printer "printer-alert" attribute defined in this document.
(b) A standard encoding of the localized ''prtAlertDescription'' columnar object in the ''prtAlertTable'' defined in IETF Printer MIB v2 (RFC 3805) into values of the new IPP Printer "printer-alert-description" attribute defined in this document.(c) A standard mapping between the device errors and warnings in the ''PrtAlertCodeTC'' textual convention defined in IANA Printer MIB and existing or new values (as needed) of the IPP Printer "printer-state-reasons" attribute defined in IPP/1.1.
(d) A standard mapping between the finishing subunit types in the ''FinDeviceTypeTC'' textual convention defined in IANA Finisher MIB (originally published in RFC 3806) and new specific values of the ''PrtAlertCodeTC'' textual convention defined in IANA Printer MIB and new values of the IPP Printer "printer-state-reasons" attribute defined in IPP/1.1, for high fidelity support of finishing alerts.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippstate10-20090731-5100.9.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippstate10-20090731-5100.9.pdf','','2009-07-31 12:00:00',1,'2009-07-31 12:00:00',1);

INSERT INTO document VALUES(10,0,2,0,5100,10,'2.0','Internet Printing Protocol 2.0 (IPP/2.0)','Since the release of IPP/1.1 (RFC 2910 and RFC 2911), numerous extensions to the IPP protocol have been published as IETF RFCs or PWG Candidate Standards. Many IPP developers are not aware of the existence of the many of these extensions, and there is no published document that references all of the extension specifications. As a consequence, only some of the extensions have been implemented.
This specification combines most of the previous IPP IETF or PWG IPP extensions into either a new base IPP/2.0 conformance level or a new extended IPP/2.1 conformance level. No new IPP functionality, beyond that defined in the previous IPP extensions, is specified in this document.
Implementation of this specification will allow printing applications to easily determine the capabilities of an IPP Printer without the need for extensive queries to the IPP Printer.','http://ftp.pwg.org/pub/pwg/candidates/cs-ipp20-20090731-5100.10.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ipp20-20090731-5100.10.pdf','','2009-07-31 12:00:00',1,'2011-02-14 12:00:00',1);

INSERT INTO document VALUES(11,0,2,10,5100,11,'1.0','IPP Job and Printer Extensions - Set 2 (JPS2)','This Job and Printer Extensions - Set 2 Specification (JPS2) defines an extension to the Internet Printing Protocol/1.0 (IPP) ([RFC2565], [RFC2566]), IPP/1.1 ([RFC2910], [RFC2911]), and [RFC3380]. This JPS2 consists of a REQUIRED Job Template attribute to print a proof print(s) of the job prior to printing the full run of the job and OPTIONAL Job Template attributes for submitting print jobs that permit a user to save jobs for later reprinting (i.e., retain the jobs indefinitely), provide a recipient name and a job phone number, provide the feed orientation, provide the font name and font size, hold a job until a specific date and time, delay the output of a job (but allow processing) until a specified time period, delay the output of a job (but allow processing) until a specific date and time, and specify an interpreter initialization file.
This JPS2 also defines four new REQUIRED operations - Cancel-Jobs, Cancel-My-Jobs, Close-Job, and Resubmit-Job - and a new REQUIRED "job-ids" operation attribute to be used with the existing Get-Jobs and Purge-Jobs operations. Cancel-Jobs allows an operator/administrator to cancel a list of Not Completed jobs or all Not Completed jobs on the Printer. Cancel-My-Jobs allows a user to cancel a list of their Not Completed jobs or all their Not Completed jobs. Close-Job allows a client to close a multi-document job without supplying any additional documents (to support streaming clients and servers). Resubmit-Job allows a user to re-process a modified copy of a Retained Job. Get-Jobs with the "job-ids" attribute allows a user to get a list of jobs. Purge-Jobs with the "job-ids" attribute allows an operator/administrator to purge a list of jobs. There are also Printer Description attributes to list the Job Creation attributes supported indicate whether jobs are spooled and list the set of media collections supported. In addition, conformance to JPS2 also REQUIRES support of the Reprocess-Job operation defined in [RFC3998].
Some of the Job Template attributes defined in this specification are also defined to be supplied as Operation attributes in the Hold-Job ([RFC2911]) and Set-Job-Attributes ([RFC3380]) operations.
In addition, OPTIONAL semantics for Attribute Precedence, a Queue Override Feature, and a feature to guarantee protocol precedence over the PDL attribute are defined.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippjobprinterext10-20101030-5100.11.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ippjobprinterext10-20101030-5100.11.pdf','','2010-10-30 12:00:00',1,'2010-10-30 12:00:00',1);

INSERT INTO document VALUES(12,10,2,10,5100,12,'2.0','IPP Version 2.0 Second Edition (IPP/2.0 SE)','Since the release of IPP/1.1 (RFC 2910 and RFC 2911), numerous extensions to the IPP protocol have been published as IETF RFCs or PWG Candidate Standards. Many IPP developers are not aware of the existence of many of these extensions, and there is no published document that references all of these extension specifications. As a consequence, only some of the extensions have been implemented.
This specification combines all of the previous IPP IETF or PWG IPP extensions into a new base IPP/2.0 conformance level and two new extended IPP/2.1 and IPP/2.2 conformance levels. No new IPP functionality is specified in this document, beyond that defined in the previous IPP extensions.
Implementation of this specification will allow printing applications to easily determine the capabilities of an IPP Printer without the need for extensive queries to the IPP Printer.','http://ftp.pwg.org/pub/pwg/candidates/cs-ipp20-20110214-5100.12.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ipp20-20110214-5100.12.pdf','','2011-02-14 12:00:00',1,'2011-02-14 12:00:00',1);

INSERT INTO document VALUES(13,0,2,10,5100,13,'1.0','IPP Job and Printer Extensions - Set 3 (JPS3)','Printing on new operating systems, distributed computing systems, and mobile devices emphasizes the challenges of generating document data, discovering available Printers, and communicating that document data to a Printer. This specification adds additional attributes and operations to IPP to better support generic, vendor-neutral implementations of printing in these environments.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippjobprinterext3v10-20120727-5100.13.docx','http://ftp.pwg.org/pub/pwg/candidates/cs-ippjobprinterext3v10-20120727-5100.13.pdf','','2012-07-27 12:00:00',1,'2012-07-27 12:00:00',1);

INSERT INTO document VALUES(14,0,2,10,5100,14,'1.0','IPP Everywhere','This standard defines an extension of IPP to support network printing without vendor-specific driver software, including the transport, various discovery protocols, and standard document formats.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippeve10-20130128-5100.14.docx','http://ftp.pwg.org/pub/pwg/candidates/cs-ippeve10-20130128-5100.14.pdf','','2013-01-28 12:00:00',1,'2013-01-28 12:00:00',1);

INSERT INTO document VALUES(15,0,2,0,5100,15,'1.0','IPP FaxOut Service','This standard defines an IPP extension to support the PWG Semantic Model FaxOut service over IPP.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfaxout10-20140618-5100.15.docx','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfaxout10-20140618-5100.15.pdf','','2013-11-15 12:00:00',1,'2013-11-15 12:00:00',1);

INSERT INTO document VALUES(16,0,2,10,5100,16,'1.0','IPP Transaction-Based Printing Extensions','This document defines extensions to the Internet Printing Protocol that support the business transaction logic needed for paid, PIN, release, and quota-based printing through local and commercial services.','http://ftp.pwg.org/pub/pwg/candidates/cs-ipptrans10-20131108-5100.16.docx','http://ftp.pwg.org/pub/pwg/candidates/cs-ipptrans10-20131108-5100.16.pdf','','2013-11-08 12:00:00',1,'2013-11-08 12:00:00',1);

INSERT INTO document VALUES(17,15,2,10,5100,15,'1.0','IPP FaxOut Service','This standard defines an IPP extension to support the PWG Semantic Model FaxOut service over IPP.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfaxout10-20140618-5100.15.docx','http://ftp.pwg.org/pub/pwg/candidates/cs-ippfaxout10-20140618-5100.15.pdf','','2014-06-18 12:00:00',1,'2014-06-18 12:00:00',1);

-- 5101.x
INSERT INTO document VALUES(18,0,1,0,5101,1,'1.0','Standard for Media Standardized Names','This document specifies standard names to be used to indicate media types, media colors, and media sizes in other standards. These lists of names are a superset of the names that are currently presented in the Printer MIB [PRT-MIB] and the IPP Model and Semantics [IPP-MOD] documents. It is intended to supplement the currently defined lists as well as to provide a normative reference for all subsequent standards.','http://ftp.pwg.org/pub/pwg/candidates/cs-pwgmsn10-20020226-5101.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-pwgmsn10-20020226-5101.1.pdf','','2002-02-26 12:00:00',1,'2002-02-26 12:00:00',1);

INSERT INTO document VALUES(19,18,2,10,5101,1,'2.0','PWG Media Standardized Names 2.0 (MSN2)','This document defines standard colorant and media names and naming conventions to be used by other PWG specifications. These lists of names are a superset of the names that are defined in the Printer MIB [RFC3805] and various Internet Printing Protocol documents.','http://ftp.pwg.org/pub/pwg/candidates/cs-pwgmsn20-20130328-5101.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-pwgmsn20-20130328-5101.1.pdf','','2013-03-28 12:00:00',1,'2013-03-28 12:00:00',1);

INSERT INTO document VALUES(20,0,1,10,5101,2,'1.0','PWG RepertoireSupported Element','In traditional printing environments, clients rely on font downloads when they are not sure a given character is embedded in the printer. As printing moves to small clients, downloading may not be an option and clients have a need to know what characters are available in a given device.
There are many published named character repertoires, and a small client will not know about them all.
To improve operability, this document defines semantics and naming conventions to allow a printer to advertise what repertoires it supports.
The primary target of this document is printing using document formats based on XML or HTML (for example, XHTML-Print). It will be less applicable to traditional PDLs (PCL, PostScript, etc.) because they tend to have very format-specific mechanisms for managing character repertoires.','http://ftp.pwg.org/pub/pwg/candidates/cs-crrepsup10-20040201-5101.2.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-crrepsup10-20040201-5101.2.pdf','','2004-02-01 12:00:00',1,'2004-02-01 12:00:00',1);

INSERT INTO document VALUES(21,0,2,10,5101,4,'1.0','Universal Printer Description Format','This document describes the concept of a Universal Printer Description Format and the set of schemas it is based on. The schemas describe input for a driver/client to assemble general information about the device and its features, to be used in user interfaces or for printing.','http://ftp.pwg.org/pub/pwg/candidates/cs-upd10-20040526-5101.4.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-upd10-20040526-5101.4.pdf','','2004-05-26 12:00:00',1,'2004-05-26 12:00:00',1);

-- 5102.x
INSERT INTO document VALUES(22,0,1,10,5102,1,'1.0','XHTML(tm)-Print','HTML 4 is a powerful language for authoring Web content, but its design does not take into consideration issues pertinent to printers, including the implementation cost (in power, memory, etc.) of the full feature set. Printers have relatively limited resources that cannot generally afford to implement the full feature set of HTML 4.
Because there are many ways to subset HTML, there are many almost identical subsets defined by organizations and companies. Without a common base set of features, developing print applications for a wide range of printers is difficult.
XHTML-Print''s targeted usage is for printing in environments where it is not feasible or desirable to install a printer-specific driver and where some variability in the formatting of the output is acceptable.
The document type definition for XHTML-Print is implemented based on the XHTML modules defined in the W3C''s Modularization of XHTML.','','http://ftp.pwg.org/pub/pwg/candidates/cs-xpxprt10-20030331-5102.1.pdf','','2003-03-31 12:00:00',1,'2003-03-31 12:00:00',1);

INSERT INTO document VALUES(23,0,1,10,5102,2,'1.0','CSS Print Profile','This specification defines a subset of the Cascading Style Sheets Level 2 specification with additions from the proposed features of Paged Media Properties for Cascading Style Sheets Level 3, to provide a strong basis for rich printing results without a detailed understanding of each individual printer''s characteristics.
It also defines an extension set that provides stronger layout control for the printing of mixed text and images, tables and image collections.','','http://ftp.pwg.org/pub/pwg/candidates/cs-xpcssprt10-20030331-5102.3.html','','2003-03-31 12:00:00',1,'2003-03-31 12:00:00',1);

INSERT INTO document VALUES(24,0,1,10,5102,3,'1.0','Portable Document Format: Image-Streamable (PDF/is)','This document specifies an application of PDF (Portable Document Format) that has two important properties: First, it is an "image"-based format, and proper rendering of the document is represented by (binary or color) images. Second, the format is suitable for incremental generation and thus it is a "streaming" format. The subset is called "PDF/is", for "PDF Image-Streamable".','http://ftp.pwg.org/pub/pwg/candidates/cs-ifxpdfis10-20040315-5102.3.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ifxpdfis10-20040315-5102.3.pdf','','2004-03-15 12:00:00',1,'2004-03-15 12:00:00',1);

INSERT INTO document VALUES(25,0,2,10,5102,4,'1.0','PWG Raster Format','This specification defines a simple raster format to support printing, scanning, and facsimile without printer-specific driver software on resource-limited clients and printers. The format includes support for a set of standard and device color spaces and bit depths, and defines PWG Semantic Model elements and IPP attributes that enable a client to generate or request a supported raster stream.','http://ftp.pwg.org/pub/pwg/candidates/cs-ippraster10-20120420-5102.4.docx','http://ftp.pwg.org/pub/pwg/candidates/cs-ippraster10-20120420-5102.4.pdf','','2012-04-20 12:00:00',1,'2012-04-20 12:00:00',1);

-- 5104.x
INSERT INTO document VALUES(26,0,1,10,5104,2,'1.0','Print System Interface','The Print Service Interface is the set of interfaces and methods that enable a Client, such as a Printer, Mobile Device, Web Portal, or Service, to create a Print Job on a Print Service. The Print Service may be used to resolve and access the information to be printed.','http://ftp.pwg.org/pub/pwg/candidates/cs-psi10-20050225-5104.2.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-psi10-20050225-5104.2.pdf','','2005-02-25 12:00:00',1,'2005-02-25 12:00:00',1);

-- 5105.x
INSERT INTO document VALUES(27,0,3,10,5105,1,'1.0','Semantic Model v1','This document is a high level overview of the Semantic Model defined by the PWG. This document briefly describes the semantic elements defined in various PWG documents and PWG documents submitted to the IETF. The Semantic Model also incorporates additions made by other groups addressing print systems. With every semantic element included a reference is provided to the document and section that details the semantic definition.
The Semantic Model contains a high level description of the Actions that operate on the objects and attributes in the model. This document does not describe the mapping of the semantics onto a specific protocol or network environment.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm10-20040120-5105.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm10-20040120-5105.1.pdf','','2004-01-20 12:00:00',1,'2004-01-20 12:00:00',1);

-- 5106.x
INSERT INTO document VALUES(28,0,6,0,5106,1,'1.0','Standard for Imaging System Counters','This standard defines the usage counters for an Imaging System, such as a network spooler, a printer or a multifunction device, and the services such a system offers. This standard does not describe mapping of these semantics to XML Schema, MIB or any protocol. Such mappings may be provided in separate documents.','http://ftp.pwg.org/pub/pwg/candidates/cs-wimscount10-20050923-5106.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-wimscount10-20050923-5106.1.pdf','','2005-09-23 12:00:00',1,'2005-09-23 12:00:00',1);

INSERT INTO document VALUES(29,28,6,10,5106,1,'1.1','PWG Standardized Imaging System Counters','This document defines the usage counters for an Imaging System, such as a network spooler, a printer or a multifunction device, and the services such a system offers. This document does not describe mapping of these semantics to XML Schema, MIB or any protocol. Such mappings may be provided in separate documents.','http://ftp.pwg.org/pub/pwg/candidates/cs-wimscount11-20070427-5106.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-wimscount11-20070427-5106.1.pdf','','2007-04-27 12:00:00',1,'2007-04-27 12:00:00',1);

INSERT INTO document VALUES(30,0,6,10,5106,2,'1.0','Web-based Imaging Management Service','This specification defines the abstract Web-based Imaging Management Service (WIMS) protocol. This specification defines five operations initiated by a WIMS Agent (embedded in services or devices), largely in support of Schedule-oriented remote management: RegisterForManagement (Agent allows management by an identified WIMS Manager); and UnregisterForManagement (cancel Agent association with a given WIMS Manager); GetSchedule (request a Schedule of planned actions); SendReports (send normal operational message); and SendAlerts (send warning or error exception message). This specification also defines four operations initiated by a WIMS Manager to support more conventional local management: BeginManagement (Manager requests ability to manage an identified Agent); EndManagement (Manager cancels association with Agent); SetSchedule (send a Schedule of planned actions with their timetables); ExecuteAction (execute the single identified action). This specification also defines sets of monitoring, management and administration actions that can be included within a Schedule. Transport bindings for the WIMS protocol are identified in the appendix.','http://ftp.pwg.org/pub/pwg/candidates/cs-wims10-20060421-5106.2.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-wims10-20060421-5106.2.pdf','','2006-04-21 12:00:00',1,'2006-04-21 12:00:00',1);

INSERT INTO document VALUES(31,0,6,10,5106,3,'2.0','PWG Imaging System State and Counter MIB 2.0','This document defines the PWG Imaging System State and Counter (ISC) MIB v2.0 that supports monitoring of system-, service-, and subunit-level state and counters on imaging devices (dedicated systems) and imaging servers (multipurpose systems). The ISC MIB can be used for fleet management, enterprise billing, field service, and other applications. The ISC MIB is entirely freestanding, but it also facilitates use of the IETF Host Resources MIB [RFC1514] [RFC2790] and IETF Printer MIB [RFC1759] [RFC3805] for imaging device and imaging server monitoring. The ISC MIB was developed by the PWG''s Web-based Imaging Management Service (WIMS) project and is based on the PWG Imaging System Counters specification [PWG5106.1].','http://ftp.pwg.org/pub/pwg/candidates/cs-wimscountmib20-20080318-5106.3.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-wimscountmib20-20080318-5106.3.pdf','','2008-03-18 12:00:00',1,'2008-03-18 12:00:00',1);

INSERT INTO document VALUES(32,0,6,10,5106,4,'1.0','PWG Power Management Model for Imaging Systems 1.0','This document defines an abstract PWG Power Management Model for Imaging Systems (Printers, Copiers, Multifunction Devices, etc.) that extends the abstract System and Subunit objects in the PWG Semantic Model.','http://ftp.pwg.org/pub/pwg/candidates/cs-wimspower10-20110214-5106.4.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-wimspower10-20110214-5106.4.pdf','','2011-02-14 12:00:00',1,'2011-02-14 12:00:00',1);

INSERT INTO document VALUES(33,0,6,10,5106,5,'1.0','PWG Imaging System Power MIB v1.0','This document defines the PWG Imaging System Power MIB (for Printers, Copiers, Multifunction Devices, etc.) that extends IETF MIB-II [RFC1213], IETF Host Resources MIB v2 [RFC2790], IETF Printer MIB v2 [RFC3805], IETF Finisher MIB [RFC3806], and PWG Imaging System State and Counter MIB v2 [PWG5106.3].','http://ftp.pwg.org/pub/pwg/candidates/cs-wimspowermib10-20110214-5106.5.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-wimspowermib10-20110214-5106.5.pdf','','2011-02-14 12:00:00',1,'2011-02-14 12:00:00',1);

-- 5107.x
INSERT INTO document VALUES(34,0,6,10,5107,1,'1.0','PWG Printer Port Monitor MIB v1.0','This document defines the PWG Printer Port Monitor (PPM) MIB v1.0 that supports printer status monitoring, automatic installation of device drivers, and other printing applications. The PPM MIB is entirely free-standing, but it also facilitates use of the IETF Host Resources MIB (RFC1514 / RFC2790) and IETF Printer MIB (RFC1759 / RFC3805) for printer status monitoring.','http://ftp.pwg.org/pub/pwg/candidates/cs-pmpportmib10-20051025-5107.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-pmpportmib10-20051025-5107.1.pdf','','2005-10-25 12:00:00',1,'2005-10-25 12:00:00',1);

INSERT INTO document VALUES(35,0,6,10,5107,2,'1.0','PWG Command Set Format for IEEE 1284 Device ID v1.0','This document defines a standard format for the COMMAND SET capability in an IEEE 1284 Device ID [IEEE1284], for use: (a) by Imaging Systems (Printers, Copiers, Multifunction Devices, etc.) to encode their supported document formats; and (b) by Imaging Clients (workstations, mobile devices, spoolers, etc.) to decode these Imaging System supported document formats, to enable automatic device driver installation and subsequent Imaging Job submission.
This document also defines the IPP Printer Description attribute "printer-device-id" which contains an IEEE 1284 Device ID and corresponds one-to-one with the ppmPrinterIEEE1284DeviceId object
defined in the PWG Printer Port Monitor MIB [PWG5107.1].','http://ftp.pwg.org/pub/pwg/candidates/cs-pmp1284cmdset10-20100531-5107.2.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-pmp1284cmdset10-20100531-5107.2.pdf','','2010-05-31 12:00:00',1,'2010-05-31 12:00:00',1);

INSERT INTO document VALUES(36,0,6,10,5107,3,'1.0','Printer MIB and IPP MFD Alerts','This document defines an update to the IANA-PRINTER-MIB (originally published in RFC 3805) to provide support for SNMP alerts in a multifunction device (MFD) and an equivalent update to IPP “printer-state-reasons” (RFC 2911) and IPP “printer-alert” (PWG 5100.9). An MFD is typically based on a printer with added scan- and fax-specific components in order to support print, copy, scan, and facsimile (fax) services. This document defines an update to the IANA-PRINTER-MIB to provide support for new MFD components and component-specific alerts and analogous Printer extension alerts for the existing Input, Output, and MediaPath components.','http://ftp.pwg.org/pub/pwg/candidates/cs-pmpmfdalerts10-20120629-5107.3.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-pmpmfdalerts10-20120629-5107.3.pdf','','2012-06-29 12:00:00',1,'2012-06-29 12:00:00',1);

-- 5108.x
INSERT INTO document VALUES(37,0,3,10,5108,1,'1.0','PWG MFD Model and Common Semantics v1.0','This specification presents the concepts, semantics and structure of a generalized model of the hardcopy imaging services provided by a Multifunction Device (MFD), a hardcopy device also known as a Multifunction Peripheral (MFP), a Multifunction Printer (MFP) or an All-in-One. This specification is both an overall introduction to the PWG MFD Model and a description of concepts and Elements common to several MFD Services. It is intended to serve as an orientation to the separate PWG specifications defining the MFD Model. The root Element of an MFD, (i.e., System) and the individual MDF Services (e.g., Copy, Print) are more appropriately covered in their own specifications. This MFD Model and Common Semantics specification is technically aligned with a named version of the PWG MFD XML Schema.
For purposes of this modeling, the services that may be performed by an MFD are: Print, Scan, Copy, FaxIn, FaxOut, EmailIn, EmailOut, Transform and Resource.
This Document defines:
- The overall MFD model including the terminology and concepts used in the MFD Service models.
- The models of an MFD Service, Job and Document
- The “Imaging Service” complex Elements, representing structures appearing in several Services but because of XML Schema restrictions, not instantiated in any Service; the appropriate Services have parallel structures that include some Service-specific Elements.
- The Elements common to several Services, eliminating the need to repeat these definitions in each Service specification.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-mfdmodel10-20110415-5108.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-mfdmodel10-20110415-5108.1.pdf','','2011-04-15 12:00:00',1,'2011-04-15 12:00:00',1);

INSERT INTO document VALUES(38,0,3,10,5108,2,'1.0','PWG Network Scan Service Semantic Model and Service Interface v1.0','Network print devices have evolved to support additional multifunction services, in particular Scan Service. When network Scanners are installed in local office or enterprise networks, they need remote service, device, and job management capabilities so that administrators, operators, and End Users can monitor their health and status. In addition, such network Scanners need remote request for job creation capabilties so that operators ane End Users can create Scan Jobs without depending entirely on local console interfaces.&nbsp; This document defines and semantic model for service, device, and job management and request for job creation for these network Scanners.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-scan10-20090410-5108.02.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-scan10-20090410-5108.02.pdf','2009-04-10',' 12:00:00',1,'2009-04-10 12:00:00',1);

INSERT INTO document VALUES(39,0,3,10,5108,3,'1.0','PWG Network Resource Service Semantic Model and Service Interface v1.0','When network Multifunction Devices are installed in local office or enterprise networks shared by large groups of users, the ability to provide resources such as job tickets pre-configured with user’s intent (Job Resource), professionally prepared Logos, Fonts, Forms, etc, that can be reused by user’s jobs is very important for office document productivity. This specification defines a Resource Service that provides operators and users a convenient way to remotely store, manage resources so that they can be retrieved and shared later through job creation requests to other services of network Multifunction Devices.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-resource10-20090703-5108.03.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-resource10-20090703-5108.03.pdf','','2009-07-03 12:00:00',1,'2009-07-03 12:00:00',1);

INSERT INTO document VALUES(40,0,3,10,5108,4,'1.0','Copy Service Semantic Model and Service Interface v1.0','Network print devices have evolved to support additional multifunction services, in particular FaxOutService. When FaxOut Devices are installed in local office or enterprise networks, they need remote service, device, and job management capabilities so that administrators, operators, and end users can monitor their health and status. In addition, such FaxOut Devices need remote job submission capabilities so that operators and end users can create FaxOut Jobs without depending entirely on local console interfaces. This document defines a semantic model for service, device, and job management and job submission for these FaxOut Devices.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-copy10-20110610-5108.04.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-copy10-20110610-5108.04.pdf','','2011-06-10 12:00:00',1,'2011-06-10 12:00:00',1);

INSERT INTO document VALUES(41,0,3,10,5108,5,'1.0','FaxOut Service Semantic Model and Service Interface v1.0','Network print devices have evolved to support additional multifunction services, in particular FaxOutService. When FaxOut Devices are installed in local office or enterprise networks, they need remote service, device, and job management capabilities so that administrators, operators, and end users can monitor their health and status. In addition, such FaxOut Devices need remote job submission capabilities so that operators and end users can create FaxOut Jobs without depending entirely on local console interfaces. This document defines a semantic model for service, device, and job management and job submission for these FaxOut Devices.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-faxout10-20110809-5108.05.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-faxout10-20110809-5108.05.pdf','','2011-08-09 12:00:00',1,'2011-08-09 12:00:00',1);

INSERT INTO document VALUES(42,0,3,10,5108,6,'1.0','System Object and System Control Service Semantics v1.0','Network print devices have evolved to support additional functions. The Multifunction Device (MFD) includes one or more services such as print, scan, copy and facsimile. The MFD Model and Common Semantics [PWG5108.1] extends the original PWG Semantic Model v1 [PWG5105.1] from printing to all of the services that typically may be performed by an MFD. We refer to a device hosting one or more of these services as an Imaging Device.
The [PWG5108.1] model extension requires a root element to represent the Imaging Device and to move the print service to be one of the hosted imaging related services. The root of the data model is the System Object. The System Object represents the Imaging Device. The System Object contains the elements that represent the Imaging Device status, description and services. The extension of the model includes bringing in elements that until now were primarily accessed via SNMP. This includes the configuration (i.e., Subunits) and conditions that are represented in the Printer MIB [RFC3805] as entries in the Alert Table (i.e., prtAlertTable). The System Object’s elements contain information that is not visible via any individual service. For example the Imaging Device total usage counters, all conditions from every service, and all Subunits.
The Imaging Device hosts a number of services. Many of these services are document related such as print, scan and copy. This specification defines the SystemControlService. The SystemControlService is needed to start (create) services and to restart services that have been previously shut down. It is desirable to be able to monitor and manage an Imaging Device as a whole or all the document related services at once. The Imaging Device’s SystemControlService provides this functionality.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-system10-20120217-5108.06.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-system10-20120217-5108.06.pdf','','2012-02-17 12:00:00',1,'2012-02-17 12:00:00',1);

INSERT INTO document VALUES(43,0,3,10,5108,7,'1.0','PWG Print Job Ticket and Associated Capabilities Version 1.0 (PJT)','This specification provides the Job Ticket and Capabilities for the Print Service. The Print Service Capabilities are supplied by the Print Service to inform the prospective Print Job Request submitter of the PrintJobTicket elements and element values supported by the Print Service. The PrintJobTicket is supplied by the client, along with Document Data, in a Print Job Request to indicate User intent for the Print Job. The Print Service contains a default PrintJobTicket that provides defaults when the corresponding element is not included with a PrintJob Creation Request.
The PrintJobTicket datatype is used by several elements including those that: represent the defaults of the Print Service, represent the user intent in a PrintJob Creation Request, and within the resulting Job Object representing the accepted print intent. A closely related datatype, the Print Service Capabilities, represents which PrintJobTicket elements are supported and what values are permitted. The PWG Semantic Model PrintJobTicket datatype, the associated Print Service Capabilities datatypes and their various instantiations are represented as a subset of the PWG Semantic Model v2 XML Schema. This specification also identifies certain Print Service Description elements relate to formatting the Document.','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-pjt10-20120801-5108.07.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-sm20-pjt10-20120801-5108.07.pdf','','2012-08-01 12:00:00',1,'2012-08-01 12:00:00',1);

-- 5110.x
INSERT INTO document VALUES(44,0,4,0,5110,1,'1.0','PWG Hardcopy Device Health Assessment Attributes','This standard defines a set of attributes for Hardcopy Devices (HCDs) that may be used in the various network health assessment protocols to measure the fitness of a HCD to attach to the network.','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20130401-5110.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20130401-5110.1.pdf','','2013-04-01 12:00:00',1,'2013-04-01 12:00:00',1);

INSERT INTO document VALUES(45,44,4,0,5110,1,'1.1','PWG Hardcopy Device Health Assessment Attributes','This standard defines a set of attributes for Hardcopy Devices (HCDs) that may be used in the various network health assessment protocols to measure the fitness of a HCD to attach to the network.','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20140106-5110.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20140106-5110.1.pdf','','2014-01-06 12:00:00',1,'2014-01-06 12:00:00',1);

INSERT INTO document VALUES(46,45,4,10,5110,1,'1.1','PWG Hardcopy Device Health Assessment Attributes','This standard defines a set of attributes for Hardcopy Devices (HCDs) that may be used in the various network health assessment protocols to measure the fitness of a HCD to attach to the network.','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20140529-5110.1.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-idsattributes10-20140529-5110.1.pdf','','2014-05-29 12:00:00',1,'2014-05-29 12:00:00',1);

INSERT INTO document VALUES(47,0,4,10,5110,2,'1.0','PWG Hardcopy Device Health Assessment Network Access Protection Protocol Binding (HCD-NAP)','This standard is one part of a set of documentation that defines the application of security policy enforcement mechanisms to imaging devices. This document specifies how the Microsoft Network Access Protection (NAP) protocol is to be used, along with the set of health assessment attributes created especially for HCDs, to allow access to such devices based upon the locally defined security policy.','http://ftp.pwg.org/pub/pwg/candidates/cs-ids-napsoh10-20130401-5110.2.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ids-napsoh10-20130401-5110.2.pdf','','2013-04-01 12:00:00',1,'2013-04-01 12:00:00',1);

INSERT INTO document VALUES(48,0,4,10,5110,3,'1.0','PWG Common Log Format (PWG-LOG)','This standard defines a common log format for hardcopy device events that can be used with existing logging protocols such as SYSLOG. While the focus of this format is on security and auditing of devices, it also supports logging of arbitrary events such as those defined by the IPP Event Notifications and Subscriptions (RFC 3995) specification.','http://ftp.pwg.org/pub/pwg/candidates/cs-ids-log10-20130401.5110.3.doc','http://ftp.pwg.org/pub/pwg/candidates/cs-ids-log10-20130401.5110.3.pdf','','2013-04-01 12:00:00',1,'2013-04-01 12:00:00',1);

-- Informational
INSERT INTO document VALUES(49,0,1,8,0,0,'','PWG Best Practices for Use of the RepertoireSupported Element','In traditional printing environments, clients rely on font downloads when they are not sure a given character is embedded in the printer. As printing moves to small clients, downloading may not be an option and clients have a need to know what characters are available in a given device.
There are many published named character repertoires, and a small client will not know about them all.
[RS] describes the syntax and semantics for the Semantic Model element "RepertoireSupported". The current document describes Best Practices for the use of that element, in order to maximize interoperability between client devices and printers.
The reader of this document should be familiar with the terminology and concepts in [RS].','http://ftp.pwg.org/pub/pwg/informational/bp-crrepsup10-20040201.doc','http://ftp.pwg.org/pub/pwg/informational/bp-crrepsup10-20040201.pdf','','2004-02-01 12:00:00',1,'2004-02-01 12:00:00', 1);

INSERT INTO document VALUES(50,0,1,8,0,0,'','PWG IPP Fax Project: IPP Fax Requirements','This document captures the requirements for IPP Fax, both the transport and the document format. This document assumes that the reader is familiar with IPP 1.1.','http://ftp.pwg.org/pub/pwg/informational/req-ifxreq10-20040204.doc','http://ftp.pwg.org/pub/pwg/informational/req-ifxreq10-20040204.pdf','','2004-02-04 12:00:00',1,'2004-02-04 12:00:00', 1);

INSERT INTO document VALUES(51,0,1,8,0,0,'','PWG Print Service Interface (PSI) Requirements','The Print Service Interface is the set of interfaces and methods that enable a Client such as a Printer, a Mobile Device, Web Portal, or a service to set up and invoke a print job from a Print Service. The Print Service may be used to resolve and access the information to be printed.','http://ftp.pwg.org/pub/pwg/informational/req-psireq10-20040212.doc','http://ftp.pwg.org/pub/pwg/informational/req-psireq10-20040212.pdf','','2004-02-12 12:00:00',1,'2004-02-12 12:00:00', 1);

INSERT INTO document VALUES(52,0,1,8,0,0,'','PWG Multifunction Device Service Model Requirements','The Multifunction Device Service Model represents an abstraction of the characteristics, capabilities, and interfaces of each of the imaging services potentially provided by a Multifunction Device (MFD), as they are accessible to an outside client. The effort to model these services as a group and the associated effort in representing these models in a MFD Semantic Model is intended to highlight the commonality in the elements of these services while preserving the distinct functions of each service. The ultimate objective is to exploit the commonality to provide a consistent interface to services that have evolved in different environments and under different circumstances, but now are typically executed in the same device and used in the same environment.
This document provides the rationale and summarizes the requirements for the modeling activity.','http://ftp.pwg.org/pub/pwg/informational/req-mfdreq10-20100901.doc','http://ftp.pwg.org/pub/pwg/informational/req-mfdreq10-20100901.pdf','','2010-09-01 12:00:00',1,'2010-09-01 12:00:00', 1);


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
  status INTEGER NOT NULL,		-- Overall status: 0 = pending,
					-- 1 = review, 2 = approved,
					-- 3 = rejected, 4 = appealed
  organization_id INTEGER,		-- Organization ID
  contact_name VARCHAR(255) NOT NULL,	-- Person to contact
  contact_email VARCHAR(255) NOT NULL,	-- That person's email
  product_family VARCHAR(255) NOT NULL,	-- Product family
  models TEXT NOT NULL,			-- Model names, one per line
  url VARCHAR(255) NOT NULL,		-- Product/organization URL
  cert_version VARCHAR(255) NOT NULL,	-- Certification version (M.m - YYYY-MM-DD)
  used_approved BOOLEAN DEFAULT FALSE,	-- Used approved software?
  used_prodready BOOLEAN DEFAULT FALSE,	-- Used production-ready code? */
  printed_correctly BOOLEAN DEFAULT FALSE,
					-- Documents printed correctly?
  exceptions TEXT NOT NULL,		-- List of exception requests
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

DROP TABLE IF EXISTS exception;

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
