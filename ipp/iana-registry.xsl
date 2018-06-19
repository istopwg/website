<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:iana="http://www.iana.org/assignments"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

  <xsl:output method="xml"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
    doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>

  <xsl:variable name="ALPHA">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>
  <xsl:variable name="alpha">abcdefghijklmnopqrstuvwxyz</xsl:variable>

  <xsl:template match="/">
    <html>
    <xsl:apply-templates select="iana:registry" />
    </html>
  </xsl:template>

  <xsl:template match="/iana:registry">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous" />
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous" />
      <link rel="stylesheet" type="text/css" href="/dynamo/resources/pwg.css" />
      <link rel="shortcut icon" href="/dynamo/resources/pwg@2x.png" type="image/png" />
      <link rel="stylesheet" href="iana-registry.css" type="text/css"/>
      <xsl:call-template name="iana:head"/>
      <title><xsl:value-of select="iana:title" /> - Printer Working Group</title>
    </head>
    <body>
      <nav class="navbar navbar-inverse navbar-fixed-top pwg-navbar" role="navigation">
	<div class="container-fluid">
	  <div class="navbar-header">
	    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#pwg-nav-collapsible"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
	    <a class="navbar-brand" href="/"><img src="/dynamo/resources/pwg-4dark.png" alt="PWG Logo" height="27" width="28" /></a>
	  </div>
	  <div class="collapse navbar-collapse" id="pwg-nav-collapsible">
	    <ul class="nav navbar-nav">
	      <li><a href="https://www.pwg.org/dynamo/login.php?PAGE=%2Fdynamo%2Fwrap.php%2Fipp%2Findex.html"><span class="glyphicon glyphicon-user"></span> Login</a></li>
	      <li><a href="/index.html">Home</a></li>
	      <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">About <span class="caret"></span></a>
		<ul class="dropdown-menu" role="menu">
		  <li><a href="/about.html">About the PWG</a></li>
		  <li><a href="/members.html#JOINING">Joining</a></li>
		  <li><a href="/members.html">Members</a></li>
		  <li><a href="/chair/index.html">Officers</a></li>
		  <li class="divider"></li>
		  <li><a href="/bofs.html">BOF Sessions</a></li>
		  <li><a href="/mailhelp.html">Mailing Lists</a></li>
		  <li><a href="/chair/meeting-info/meetings.html">Meetings</a></li>
		  <li><a href="/chair/participating.html">Participating</a></li>
		  <li><a href="https://ieee-isto.org/privacy-policy/">Privacy Policy</a></li>
		</ul>
	      </li>
	      <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Our Work <span class="caret"></span></a>
		<ul class="dropdown-menu" role="menu">
		  <li class="dropdown-header" role="presentation">Publications</li>
		  <li><a href="/informational.html">Informational Documents</a></li>
		  <li><a href="/namespaces.html">Namespaces</a></li>
		  <li><a href="/standards.html">Standards</a></li>
		  <li class="divider"></li>
		  <li class="dropdown-header" role="presentation">Technologies</li>
		  <li><a href="/3d/index.html">3D Printing</a></li>
		  <li><a href="/ipp/everywhere.html">IPP Everywhere&#x2122;</a></li>
		  <li><a href="/sm/model.html">PWG Semantic Model</a></li>
		</ul>
	      </li>
	      <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Workgroups <span class="caret"></span></a>
		<ul class="dropdown-menu" role="menu">
		  <li class="dropdown-header" role="presentation">Active Workgroups</li>
		  <li><a href="/ids/">Imaging Device Security</a></li>
		  <li><a href="/ipp/">Internet Printing Protocol</a></li>
		  <li class="divider"></li>
		  <li class="dropdown-header" role="presentation">Inactive Workgroups</li>
		  <li><a href="/cloud/">Cloud Imaging Model</a></li>
		  <li><a href="/sm/">Semantic Model</a></li>
		  <li><a href="/wims/">Workgroup for Imaging Management Solutions</a></li>
		</ul>
	      </li>
	    </ul>
	  </div>
	</div>
      </nav>
      <div id="pwg-body">
	<div id="pwg-content">
	  <xsl:apply-templates select="iana:title" />
	  <xsl:if test="iana:created|iana:updated|iana:registration_rule|iana:expert|iana:description|iana:note|iana:xref|iana:record">
	    <dl>
	      <xsl:apply-templates select="iana:created" />
	      <xsl:apply-templates select="iana:updated" />
	      <xsl:apply-templates select="iana:registration_rule" />
	      <xsl:apply-templates select="iana:expert" />
	      <xsl:apply-templates select="iana:description" />
	      <xsl:call-template name="iana:references"/>
	      <xsl:apply-templates select="iana:note" />
	      <xsl:call-template name="iana:formats"/>
	    </dl>
	  </xsl:if>
	  <xsl:if test="iana:registry and not(iana:file)">
	    <xsl:choose>
	      <xsl:when test="count(iana:registry/iana:title) = 0">
	      </xsl:when>
	      <xsl:when test="count(iana:registry/iana:title) = 1">
		<p><b>Registry included below</b></p>
	      </xsl:when>
	      <xsl:otherwise>
		<p><b>Registries included below</b></p>
	      </xsl:otherwise>
	    </xsl:choose>
	    <xsl:call-template name="table-of-contents"/>
	  </xsl:if>
	  <xsl:if test="iana:pagination/@page_cnt > 1 or iana:pagination/@search">
	    <form method="get">
	      <xsl:attribute name="action"><xsl:value-of select="iana:pagination/iana:url"/></xsl:attribute>
	      <input name="search" size="18" type="text">
		<xsl:attribute name="value"><xsl:value-of select="iana:pagination/@search"/></xsl:attribute>
	      </input>
	      <input value="Search" type="submit"/>
	    </form>
	  </xsl:if>
	  <xsl:if test = "iana:record"><xsl:call-template name="iana:records" /></xsl:if>
	  <xsl:if test = "iana:file"><h2>Files</h2><xsl:call-template name="iana:files" /></xsl:if>
	  <xsl:apply-templates select="iana:registry" />
	  <xsl:apply-templates select="iana:people"/>
	  <xsl:call-template name="iana:footnotes"/>
	</div>
      </div>
      <div id="pwg-footer">
	<div id="pwg-footer-body">Comments are owned by the poster. All other material is Copyright &#x00a9; 2001-2018 The Printer Working Group. All rights reserved. IPP Everywhere, the IPP Everywhere logo, and the PWG logo are trademarks of the IEEE-ISTO.<br />
      <a href="/about.html">About the PWG</a> &#x00b7; <a href="https://ieee-isto.org/privacy-policy/">Privacy Policy</a> &#x00b7; <a href="mailto:webmaster@pwg.org">PWG Webmaster</a></div>
      </div>

      <script src="https://code.jquery.com/jquery-3.2.1.min.js"   integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
      <script type="text/javascript" src="/dynamo/resources/pwg.js"></script>
      <script type="text/javascript" src="/dynamo/resources/pwg-cookie-notice.js"></script>
      <!-- IE insists on having <script ...></script>, not <script .../> when it
      displays XML converted on the fly using XSLT. -->
      <script type="text/javascript" src="sort.js"></script>
    </body>
  </xsl:template>

  <xsl:template name="iana:head">
  </xsl:template>

  <xsl:template match="/iana:registry/iana:people">
    <xsl:if test="iana:person">
      <h1 class="people">People</h1>
      <table class="sortable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <xsl:if test="iana:person/iana:org">
              <th>Organization</th>
            </xsl:if>
            <th>Contact URI</th>
            <th>Last Updated</th>
          </tr>
        </thead>
        <tbody>
          <xsl:apply-templates select="iana:person"/>
        </tbody>
      </table>
    </xsl:if>
  </xsl:template>

  <xsl:template match="/iana:registry/iana:people/iana:person">
    <tr>
      <td><a name="{@id}">[<xsl:value-of select="@id"/>]</a></td>
      <td><xsl:value-of select="iana:name"/></td>
      <xsl:if test="../iana:person/iana:org">
        <td><xsl:value-of select="iana:org"/></td>
      </xsl:if>
      <td>
        <xsl:for-each select="iana:uri">
          <a href="{.}"><xsl:value-of select="."/></a>
          <xsl:if test="position() != last()"><br/></xsl:if>
        </xsl:for-each>
      </td>
      <td><xsl:value-of select="iana:updated"/></td>
    </tr>
  </xsl:template>

  <xsl:template name="table-of-contents">
    <xsl:if test="iana:registry[iana:title]">
      <ul>
        <xsl:for-each select="iana:registry[iana:title]">
          <li>
            <a href="#{@id}"><xsl:value-of select="iana:title"/></a>
            <xsl:choose>
              <xsl:when test="iana:registry[iana:title]">
                <xsl:call-template name="table-of-contents"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:for-each
                  select="following-sibling::iana:registry[position()=1][count(iana:title)=0]">
                  <xsl:call-template name="table-of-contents"/>
                </xsl:for-each>
              </xsl:otherwise>
            </xsl:choose>
          </li>
        </xsl:for-each>
      </ul>
    </xsl:if>
  </xsl:template>

  <xsl:template name="iana:references">
    <xsl:if test="iana:xref">
      <dt>Reference</dt>
      <dd><xsl:apply-templates select="iana:xref"/></dd>
    </xsl:if>
  </xsl:template>

  <xsl:template match="/iana:registry/iana:title">
    <h1><xsl:apply-templates select="child::node()" /></h1>
  </xsl:template>

  <xsl:template match="iana:title">
    <xsl:choose>
      <xsl:when test="count(ancestor::node()) = 2">
        <h1><a name="{../@id}"/><xsl:apply-templates select="child::node()"/></h1>
      </xsl:when>
      <xsl:when test="count(ancestor::node()) = 3">
        <h2><a name="{../@id}"/><xsl:apply-templates select="child::node()"/></h2>
      </xsl:when>
      <xsl:when test="count(ancestor::node()) = 4">
        <h3><a name="{../@id}"/><xsl:apply-templates select="child::node()"/></h3>
      </xsl:when>
      <xsl:when test="count(ancestor::node()) = 5">
        <h4><a name="{../@id}"/><xsl:apply-templates select="child::node()"/></h4>
      </xsl:when>
      <xsl:when test="count(ancestor::node()) = 6">
        <h5><a name="{../@id}"/><xsl:apply-templates select="child::node()"/></h5>
      </xsl:when>
      <xsl:otherwise>
        <h6><a name="{../@id}"/><xsl:apply-templates select="child::node()"/></h6>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="iana:artwork">
    <pre><xsl:value-of select="." /></pre>
  </xsl:template>

  <xsl:template match="iana:registry/iana:registration_rule">
    <dt>Registration Procedures</dt><dd><pre><xsl:apply-templates/></pre></dd>
  </xsl:template>

  <xsl:template match="iana:registry/iana:expert">
    <dt>Expert(s)</dt><dd><pre><xsl:apply-templates/></pre></dd>
  </xsl:template>

  <xsl:template match="iana:registry/iana:created">
    <dt>Created</dt><dd><xsl:value-of select="." /></dd>
  </xsl:template>

  <xsl:template match="iana:registry/iana:updated">
    <dt>Last Updated</dt><dd><xsl:value-of select="." /></dd>
  </xsl:template>

  <xsl:template match="iana:registry/iana:description">
    <dt>Description</dt><dd><pre><xsl:apply-templates/></pre></dd>
  </xsl:template>

  <xsl:template match="iana:registry/iana:note">
    <dt>Note</dt>
    <dd>
      <xsl:choose>
        <xsl:when test="@format = 'rich'">
          <xsl:apply-templates/>
        </xsl:when>
        <xsl:otherwise>
          <pre><xsl:apply-templates/></pre>
        </xsl:otherwise>
      </xsl:choose>
    </dd>
  </xsl:template>

  <xsl:template name="iana:notes">
    <xsl:choose>
      <xsl:when test="count(iana:note) > 1">
        <ul>
          <xsl:for-each select="iana:note">
            <li><xsl:apply-templates/></li>
          </xsl:for-each>
        </ul>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates select="iana:note"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="iana:note">
        <xsl:apply-templates/>
  </xsl:template>

  <xsl:template name="iana:formats" />

  <xsl:template match="iana:registry">
    <xsl:apply-templates select="iana:title"/>
    <xsl:if
      test="iana:registration_rule|iana:expert|iana:description|iana:note|iana:xref|iana:record">
      <dl>
        <xsl:apply-templates select="iana:registration_rule" />
        <xsl:apply-templates select="iana:expert" />
        <xsl:apply-templates select="iana:description" />
        <xsl:call-template name="iana:references"/>
        <xsl:apply-templates select="iana:note" />
        <xsl:if test="iana:record"><xsl:call-template name="iana:formats"/></xsl:if>
      </dl>
    </xsl:if>
    <xsl:call-template name="iana:records" />
  </xsl:template>

  <xsl:template name="iana:record_style"/>
  <xsl:template name="iana:record_header"/>

  <xsl:template name="page_link">
    <xsl:param name="i"/>
    <a>
      <xsl:attribute name="href">
        <xsl:value-of select="iana:url"/>
        <xsl:text>&amp;page=</xsl:text>
        <xsl:value-of select="$i"/>
      </xsl:attribute>
      <xsl:value-of select="$i"/>
    </a>
  </xsl:template>

  <xsl:template name="page_bar">
    <xsl:param name="max"/>
    <xsl:param name="i"/>
    <xsl:choose>
      <xsl:when test="$i &lt; $max">
        <xsl:text> </xsl:text>
        <xsl:call-template name="page_link"><xsl:with-param name="i" select="$i"/></xsl:call-template>
        <xsl:call-template name="page_bar">
          <xsl:with-param name="i" select="$i + 1"/>
          <xsl:with-param name="max" select="$max"/>
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:variable name="nav_width" select="5"/>
  <xsl:template match="iana:pagination">
    <xsl:if test="@page_num != 1">
      <xsl:call-template name="page_link"><xsl:with-param name="i" select="1"/></xsl:call-template>
    </xsl:if>
    <xsl:choose>
      <xsl:when test="(@page_num - $nav_width) &gt; 0">
        <xsl:text>...</xsl:text>
        <xsl:call-template name="page_bar">
          <xsl:with-param name="max" select="@page_num"/>
          <xsl:with-param name="i" select="@page_num - $nav_width"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="page_bar">
          <xsl:with-param name="max" select="@page_num"/>
          <xsl:with-param name="i" select="2"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text> </xsl:text><xsl:value-of select="@page_num"/>
    <xsl:choose>
      <xsl:when test="(@page_num + $nav_width) &lt; @page_cnt">
        <xsl:call-template name="page_bar">
          <xsl:with-param name="max" select="@page_num + $nav_width + 1"/>
          <xsl:with-param name="i" select="@page_num + 1"/>
        </xsl:call-template>
        <xsl:text> ...</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="page_bar">
          <xsl:with-param name="max" select="@page_cnt"/>
          <xsl:with-param name="i" select="@page_num + 1"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text> </xsl:text>
    <xsl:if test="@page_cnt != @page_num">
      <xsl:call-template name="page_link"><xsl:with-param name="i" select="@page_cnt"/></xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="iana:records">
    <xsl:if test="iana:range">
       <table class="sortable" id="table-{@id}-range">
          <thead>
	     <tr>
                <xsl:choose>
                   <xsl:when test="iana:range/iana:hex">
                      <th>Decimal</th>
	              <th>Hex</th>
                   </xsl:when>
                   <xsl:otherwise>
                      <th>Range</th>
                   </xsl:otherwise>
                </xsl:choose>
		<th>Registration Procedures</th>
                <xsl:if test="iana:range/iana:note">
	           <th>Note</th>
                </xsl:if>
                <xsl:if test="iana:range/iana:xref">
                   <th>References</th>
                </xsl:if>
             </tr>
	  </thead>
	  <tbody>
             <xsl:for-each select="iana:range">
                <tr>
                   <td align="center"><xsl:value-of select="iana:value"/></td>
                   <xsl:if test="../iana:range/iana:hex">
                      <td><xsl:apply-templates select="iana:hex"/></td>
                   </xsl:if>
                   <td><xsl:apply-templates select="iana:registration_rule"/></td>
                   <xsl:if test="../iana:range/iana:note">
                      <td><xsl:apply-templates select="iana:note"/></td>
                   </xsl:if>
                   <xsl:if test="../iana:range/iana:xref">
                      <td><xsl:apply-templates select="iana:xref"/></td>
                   </xsl:if>
                </tr>
             </xsl:for-each>
	  </tbody>
       </table>
    </xsl:if>
    <xsl:if test="iana:pagination/@page_cnt > 1">
      <div class="pagination"><xsl:apply-templates select="iana:pagination"/></div>
    </xsl:if>
    <xsl:choose>
      <xsl:when test="iana:record|iana:registry|iana:artwork">
        <xsl:if test="iana:record">
          <table id="table-{@id}">
            <xsl:attribute name="class">sortable<xsl:if test="iana:pagination">_srv</xsl:if></xsl:attribute>
	    <xsl:call-template name="iana:record_style"/>
            <thead>
              <xsl:call-template name="iana:record_header"/>
              <xsl:if test="iana:record//iana:unallocated">
                <tr>
                  <td colspan="0">
                    <input type="checkbox" class="unallocatedcb" id="unallocatedcb-{@id}" checked="checked" value="1"/>
                    <label for="unallocatedcb-{@id}">Show unallocated space</label>
                  </td>
                </tr>
              </xsl:if>
            </thead>
            <tbody>
              <xsl:apply-templates select="iana:record"/>
            </tbody>
          </table>
        </xsl:if>
        <xsl:if test="iana:registry">
          <div class="registry level{count(ancestor::node())}">
            <xsl:apply-templates select="iana:registry" />
          </div>
        </xsl:if>
        <xsl:if test="iana:artwork">
          <xsl:apply-templates select="iana:artwork"/>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
       <xsl:call-template name="iana:registryempty"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="iana:pagination/@page_cnt > 1">
      <div class="pagination"><xsl:apply-templates select="iana:pagination"/></div>
    </xsl:if>
  </xsl:template>

  <xsl:template name="iana:files">
    <ul>
      <xsl:for-each select="iana:file">
        <li>
          <xsl:apply-templates select="."/>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:template>

  <xsl:template match="iana:file">
    <a>
      <xsl:attribute name="href">
        <xsl:value-of select="."/>
      </xsl:attribute>
      <xsl:choose>
        <xsl:when test="@name">
          <xsl:value-of select="@name"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </a>
  </xsl:template>

  <xsl:template name="iana:footnotes">
    <xsl:if test="//iana:footnote">
      <h1>Footnote<xsl:if test="count(//iana:footnote) != 1">s</xsl:if></h1>
      <table class="fn">
        <xsl:apply-templates select="//iana:footnote"/>
      </table>
    </xsl:if>
  </xsl:template>

  <xsl:template match="iana:footnote">
    <tr>
      <td class="fn" valign="top"><a name="note{@anchor}">[<xsl:value-of select="@anchor"/>]</a></td>
      <td class="fn"><pre><xsl:apply-templates/></pre></td>
    </tr>
  </xsl:template>

  <xsl:template match="iana:xref">
    <xsl:text>[</xsl:text>
    <xsl:choose>
      <xsl:when test="@type = 'rfc'">
        <a href="http://www.iana.org/go/{@data}">
          <xsl:choose>
            <xsl:when test="normalize-space()">
              <xsl:value-of select="."/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="translate(@data,$alpha,$ALPHA)"/>
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </xsl:when>
      <xsl:when test="@type = 'rfc-errata'">
        <a href="http://www.rfc-editor.org/errata_search.php?eid={@data}"><span>RFC Errata </span>
          <xsl:choose>
            <xsl:when test="normalize-space()">
              <xsl:value-of select="."/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="translate(@data,$alpha,$ALPHA)"/>
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </xsl:when>
      <xsl:when test="@type = 'draft'">
        <a>
          <xsl:attribute name="href">
            <xsl:choose>
              <xsl:when test="starts-with(@data, 'RFC-')">http://www.iana.org/go/draft-<xsl:value-of select="substring(@data,5)"/></xsl:when>
              <xsl:otherwise>http://www.iana.org/go/<xsl:value-of select="@data"/></xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:choose>
            <xsl:when test="normalize-space()">
              <xsl:value-of select="."/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="@data"/>
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </xsl:when>
      <xsl:when test="@type = 'uri'">
        <a href="{@data}">
          <xsl:choose>
            <xsl:when test="normalize-space()">
              <xsl:value-of select="."/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="@data"/>
            </xsl:otherwise>
          </xsl:choose>
        </a>
      </xsl:when>
      <xsl:when test="@type = 'person'">
        <a href="#{@data}"><xsl:value-of select="@data"/></a>
      </xsl:when>
      <xsl:when test="@type = 'note'">
        <a href="#note{@data}"><xsl:value-of select="@data"/></a>
      </xsl:when>
      <xsl:when test="@type = 'registry'">
        <a href="http://www.iana.org/assignments/{@data}">
          <xsl:choose>
            <xsl:when test="normalize-space()">
              <xsl:value-of select="."/>
            </xsl:when>
            <xsl:otherwise>IANA registry <i><xsl:value-of select="@data"/></i></xsl:otherwise>
          </xsl:choose>
        </a>
      </xsl:when>
      <xsl:when test="@type = 'unicode'">
        <xsl:choose>
          <xsl:when test="starts-with(@data, 'ucd')">
            <a href="http://unicode.org/Public/{substring-after(@data, 'ucd')}">
              <xsl:choose>
                <xsl:when test="normalize-space()">
                  <xsl:value-of select="."/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:text>Unicode Character Database </xsl:text>
                  <xsl:value-of select="substring-after(@data, 'ucd')"/>
                </xsl:otherwise>
              </xsl:choose>
            </a>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="."/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>]</xsl:text>
    <xsl:if test="@lastupdated">
      (<xsl:value-of select="@lastupdated"/>)
    </xsl:if>
  </xsl:template>

  <xsl:template match="iana:br">
    <br/>
  </xsl:template>

  <xsl:template match="iana:paragraph">
    <p><xsl:apply-templates select="child::node()"/></p>
  </xsl:template>

  <xsl:template match="iana:list">
    <ul><xsl:apply-templates select="child::node()"/></ul>
  </xsl:template>

  <xsl:template match="iana:item">
    <li><xsl:apply-templates select="child::node()"/></li>
  </xsl:template>

  <xsl:template name="iana:registryempty">
   <table>
     <thead>
       <xsl:call-template name="iana:record_header"/>
     </thead>
     <tbody>
       <tr>
         <td colspan="0" class="registryempty">No registrations at this time.</td>
       </tr>
     </tbody>
   </table>
  </xsl:template>

  <xsl:template name="bitvalue-recur">
    <xsl:param name="number"/>
    <xsl:param name="width"/>
    <xsl:variable name="digits" select="'0123456789ABCDEF'"/>
    <xsl:choose>
      <xsl:when test="$width = 0">
        <xsl:if test="$number >= 16">
          <xsl:call-template name="bitvalue-recur">
            <xsl:with-param name="number" select="floor($number div 16)"/>
          </xsl:call-template>
        </xsl:if>
        <xsl:value-of select="substring($digits, ($number mod 16) + 1, 1)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:if test="$width > 1">
          <xsl:call-template name="bitvalue-recur">
            <xsl:with-param name="width" select="$width - 1"/>
            <xsl:with-param name="number" select="floor($number div 16)"/>
          </xsl:call-template>
        </xsl:if>
        <xsl:value-of select="substring($digits, ($number mod 16) + 1, 1)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template name="bitvalue">
    <xsl:param name="base" select="2"/>
    <xsl:param name="power"/>
    <xsl:param name="value" select="1"/>
    <xsl:choose>
      <xsl:when test="$power > 0">
        <xsl:call-template name="bitvalue">
          <xsl:with-param name="base" select="$base"/>
          <xsl:with-param name="power" select="$power - 1"/>
          <xsl:with-param name="value" select="$value * $base"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>0x</xsl:text>
        <xsl:call-template name="bitvalue-recur">
          <xsl:with-param name="number" select="$value"/>
          <xsl:with-param name="width" select="2"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
