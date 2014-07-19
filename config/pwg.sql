--
-- "$Id$"
--
-- Database schema for the PWG web pages.
--


--
-- Site Features:
--
--   - Users
--   - Blog articles and user-editable content/workgroup pages
--   - Calendar
--   - Issues
--   - Certified printers
--   - Pending certifications (submissions)
--   - Comments (attached to pretty much anything)
--   - RSS feed for blog and certified printers
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
  is_published BOOLEAN DEFAULT FALSE,	-- Show on users page?
  email VARCHAR(255) UNIQUE NOT NULL,	-- Email address
  name VARCHAR(255) NOT NULL,		-- Real name
  organization VARCHAR(255) NOT NULL,	-- Organization/company
  hash CHAR(128) NOT NULL,		-- crypt(password,sha512salt)
  is_admin BOOLEAN DEFAULT FALSE,	-- FALSE/0 = user, TRUE/1 = admin
  is_member BOOLEAN DEFAULT FALSE,	-- FALSE/0 = not PWG member, TRUE/1 = PWG member
  is_reviewer BOOLEAN DEFAULT FALSE,	-- FALSE/0 = cannot review, TRUE/1 = can review
  is_submitter BOOLEAN DEFAULT FALSE,	-- FALSE/0 = cannot submit, TRUE/1 = can submit
  timezone VARCHAR(255) NOT NULL,	-- Timezone for user
  itemsperpage INTEGER DEFAULT 10 NOT NULL,
					-- Default items per page
  question1 VARCHAR(255),		-- First security question
  answer1 VARCHAR(255),			-- First security answer
  question2 VARCHAR(255),		-- Second security question
  answer2 VARCHAR(255),			-- Second security answer
  question3 VARCHAR(255),		-- Third security question
  answer3 VARCHAR(255),			-- Third security answer
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER,			-- User that created the user
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER,			-- User that made the last change

  INDEX(organization),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- INSERT INTO user VALUES(1, 2, 'michael.r.sweet@gmail.com','Michael Sweet','$6$b1963065bac3a508$uli/BFat3xp8FNEQ6ZGmvCW3KVMseVkabOFHLMIRAFjNVXmJa.z36LX.pYJPqty4aczuD1hxtLP6AM.O8L2.61',TRUE,999999,'America/Toronto',50,'','','','','','','1969-11-20 12:00:00',1,'1969-11-20 12:00:00',1);


--
-- Schema for table 'workgroup'
--
-- This table lists the PWG workgroups
--

DROP TABLE IF EXISTS workgroup;
CREATE TABLE workgroup (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  is_published BOOLEAN DEFAULT FALSE,	-- Show as active?
  name VARCHAR(255) NOT NULL,		-- Workgroup name
  dirname VARCHAR(255) NOT NULL,	-- Directory name
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
INSERT INTO workgroup VALUES(1, 1, 'Internet Printing Protocol (IPP)', 'ipp', '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(2, 1, 'Semantic Model (SM)', 'sm3', '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(3, 1, 'Imaging Device Security (IDS)', 'ids', '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(4, 1, 'Cloud Imaging Model', 'cloud', '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);
INSERT INTO workgroup VALUES(5, 0, 'Workgroup for Imaging Management Solutions (WIMS)', 'wims', '1991-01-01 00:00:00',1,'1991-01-01 00:00:00',1);


--
-- Schema for table 'page'
--
-- This table lists the available blog articles and pages for each workgroup.
--

DROP TABLE IF EXISTS page;
CREATE TABLE page (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  is_published BOOLEAN DEFAULT FALSE,	-- FALSE/0 = private, TRUE/1 = public
  workgroup_id INTEGER,			-- Workgroup, if any
  type INTEGER,				-- 0 = long-term content, 1 = blog article
  title VARCHAR(255) NOT NULL,		-- Title of page
  contents TEXT NOT NULL,		-- Contents of page
  filename VARCHAR(255) UNIQUE NOT NULL,-- Filename link
  altname VARCHAR(255) NOT NULL,	-- Alternate filename, if any
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that created the article
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(workgroup_id),
  INDEX(type),
  INDEX(filename),
  INDEX(altname),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Schema for table 'document'
--
-- This table stores published documents.
--

DROP TABLE IF EXISTS document;
CREATE TABLE document (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- ID
  workgroup_id INTEGER,			-- Workgroup, if any
  status INTEGER,			-- 0 = withdrawn, 1 = informational,
					-- 2 = candidate standard, 3 = standard
  number VARCHAR(255) NOT NULL,		-- PWG document number, if any (5100.1, etc.)
  version VARCHAR(255) NOT NULL,	-- Document version number, if any (1.0, etc.)
  title VARCHAR(255) NOT NULL,		-- Title of document
  contents TEXT NOT NULL,		-- Abstract of document
  url VARCHAR(255) NOT NULL,		-- Published URL
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
  is_published BOOLEAN DEFAULT FALSE,	-- FALSE/0 = private, TRUE/1 = public
  status INTEGER,			-- 0 = new, 1 = pending, 2 = active
					-- 3 = closed/resolved,
					-- 4 = closed/unresolved
  priority INTEGER,			-- 0 = unassigned, 1 = critical, 2 = high,
					-- 3 = moderate, 4 = low, 5 = enhancement
  title VARCHAR(255) NOT NULL,		-- Plain text summary
  contents TEXT NOT NULL,		-- Description of issue
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
  parent_id INTEGER,			-- Parent comment or 0
  ref_id VARCHAR(255),			-- Reference ("table_id")
  is_published BOOLEAN DEFAULT FALSE,	-- FALSE/0 = private, TRUE/1 = public
  contents TEXT NOT NULL,		-- Text message
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that posted the text
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(parent_id),
  INDEX(ref_id),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Schema for table 'manufacturer'
--
-- This table tracks the manufacturers associated with certifications.
--

DROP TABLE IF EXISTS manufacturer;
CREATE TABLE manufacturer (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Manufacturer ID
  is_published BOOLEAN DEFAULT FALSE,	-- FALSE/0 = private, TRUE/1 = public
  name VARCHAR(255) NOT NULL,		-- Manufacturer name
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that submitted the printer
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

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
  manufacturer_id INTEGER,		-- Manufacturer ID
  contact_name VARCHAR(255) NOT NULL,	-- Person to contact
  contact_email VARCHAR(255) NOT NULL,	-- That person's email
  product_family VARCHAR(255) NOT NULL,	-- Product family
  models TEXT NOT NULL,			-- Model names, one per line
  url VARCHAR(255) NOT NULL,		-- Product/manufacturer URL
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
-- Schema for table 'calendar'
--
-- This table contains the calendar events for meetings, etc.
--

DROP TABLE IF EXISTS calendar;
CREATE TABLE calendar (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,-- Printer ID
  status INTEGER NOT NULL,		-- 0 = tentative, 1 = confirmed, 2 = canceled
  workgroup_id INTEGER,			-- Workgroup, if any
  date DATETIME NOT NULL,		-- Date of event
  duration INTEGER NOT NULL,		-- Duration of event in minutes
  title VARCHAR(255) NOT NULL,		-- Title/summary of event
  contents TEXT NOT NULL,		-- Text/description of event
  create_date DATETIME NOT NULL,	-- Time/date of creation
  create_id INTEGER NOT NULL,		-- User that submitted the printer
  modify_date DATETIME NOT NULL,	-- Time/date of last change
  modify_id INTEGER NOT NULL,		-- User that made the last change

  INDEX(workgroup_id),
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
  is_published BOOLEAN DEFAULT FALSE,	-- FALSE/0 = private, TRUE/1 = public
  submission_id INTEGER NOT NULL,	-- Submission ID for this entry
  manufacturer_id INTEGER,		-- Manufacturer ID
  product_family VARCHAR(255) NOT NULL,	-- Product family
  model VARCHAR(255) NOT NULL,		-- Model name
  url VARCHAR(255) NOT NULL,		-- Product/manufacturer URL
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
  INDEX(manufacturer_id),
  INDEX(product_family),
  INDEX(cert_version),
  INDEX(color_supported),
  INDEX(duplex_supported),
  INDEX(finishings_supported),
  INDEX(create_id),
  INDEX(modify_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- End of "$Id$".
--
