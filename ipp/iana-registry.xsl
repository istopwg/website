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
      <link rel="stylesheet" href="iana-registry.css" type="text/css"/>
      <!-- IE insists on having <script ...></script>, not <script .../> when it
      displays XML converted on the fly using XSLT. -->
      <script type="text/javascript" src="jquery.js"></script>
      <script type="text/javascript" src="sort.js"></script>
      <xsl:call-template name="iana:head"/>
      <title><xsl:value-of select="iana:title" /></title>
    </head>
    <body>
      <xsl:apply-templates select="iana:title" />
      <xsl:if
        test="iana:created|iana:updated|iana:description|iana:note|iana:xref">
        <dl>
          <xsl:apply-templates select="iana:created" />
          <xsl:apply-templates select="iana:updated" />
          <xsl:apply-templates select="iana:registration_rule" />
          <xsl:apply-templates select="iana:description" />
          <xsl:call-template name="iana:references"/>
          <xsl:apply-templates select="iana:note" />
        </dl>
      </xsl:if>
      <!--<p>This registry is also available in <a href="{@id}.txt">plain text</a>.</p>-->
      <xsl:if test="iana:registry">
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
      <xsl:if test = "iana:record"><xsl:call-template name="iana:records" /></xsl:if>
      <xsl:apply-templates select="iana:registry" />
      <xsl:if test="iana:footnote">
        <xsl:call-template name="iana:footnotes"/>
      </xsl:if>
      <xsl:apply-templates select="iana:people"/>
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
    <dt>Note</dt><dd><pre><xsl:apply-templates/></pre></dd>
  </xsl:template>

  <xsl:template match="iana:registry">
    <xsl:apply-templates select="iana:title"/>
    <xsl:if test="iana:registration_rule|iana:description|iana:note|iana:xref">
      <dl>
        <xsl:apply-templates select="iana:registration_rule" />
        <xsl:apply-templates select="iana:description" />
        <xsl:call-template name="iana:references"/>
        <xsl:apply-templates select="iana:note" />
      </dl>
    </xsl:if>
    <xsl:call-template name="iana:records" />
    <xsl:if test="iana:footnote">
      <xsl:call-template name="iana:footnotes"/>
    </xsl:if>
  </xsl:template>

  <xsl:template name="iana:record_style"/>
  <xsl:template name="iana:record_header"/>

  <xsl:template name="iana:records">
    <xsl:choose>
      <xsl:when test="iana:record|iana:registry|iana:artwork">
        <xsl:if test="iana:record">
          <table class="sortable" id="table-{@id}">
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
  </xsl:template>

  <xsl:template name="iana:footnotes">
    <h2>Footnotes</h2>
    <table class="fn">
      <xsl:apply-templates select="iana:footnote"/>
    </table>
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
        <a href="http://www.rfc-editor.org/errata_search.php?eid={@data}">RFC Errata
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

  <xsl:template name="iana:registryempty">
   <table>
    <tr>
     <td colspan="0">
      <i>Registry is empty.</i>
     </td>
    </tr>
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
