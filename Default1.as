<script runat=server>
/****************************** Module Header ****************************** * Module Name:  ADAuthentication Project (ADAuth) Web Service * Project:      ADAuth Web Service to verify user's login and password details in the Active Directory * * Default.Aspx page to check if the posted form with username and password is valid in the AD * * Revisions: *     1. Sundar Krishnamurthy         sundar_k=@hotmail.com               11/03/2016       Initial file created. ***************************************************************************/
</script>
<%@ Page language="c#" AutoEventWireup="true" %>
<%@ Import Namespace="System.DirectoryServices" %>
<%@ Import Namespace="System.DirectoryServices.AccountManagement" %>
<%@ Import Namespace="System.IO" %>
<%@ Import Namespace="System.Net" %>
<%@ Import Namespace="System.Text" %>
<script runat=server>
    /// <summary>
    /// Page Load event - check if this is a post, and if so, process it to verify user details in AD
    /// </summary>
    /// <param name="sender">Sender instance</param>
    /// <param name="e">EventArgs instance</param>
    void Page_Load(object sender, EventArgs e) {

        var responseString = string.Empty;

        // If user is performing a POST
        if (Request.HttpMethod == "POST") {
            // And you have a user name (login samAccountName or email address, with valid password
            if ((Request.Form["name"] != null) && (Request.Form["password"] != null)) {

                var name = Request.Form["name"].ToLowerInvariant().Trim();
                var password = Request.Form["password"];

                // Will be set to true if we have a valid login and password
                var responseFlag = false;

                var firstName = null as string;
                var lastName = null as string;
                var email = null as string;
                var samAccountName = null as string;

                // If user has furnished an email address
                if (name.IndexOf('@') > 0) {

                    email = name;

                    // Proceed only for company email addresses - this should be @yourcompany.com
                    if (email.EndsWith("$$YOUR_EMAIL_DOMAIN$$")) {

                        // Construct context to query your Active Directory
                        using (var context = new System.DirectoryServices.AccountManagement.PrincipalContext(ContextType.Domain, "$$YOUR_AD_DOMAIN$$")) {

                            // Construct UserPrincipal object for this context, with a search for email address
                            var userPrincipal = new UserPrincipal(context);
                            userPrincipal.EmailAddress = name;

                            // Search and find this user in the system – PrincipalSearcher instance for what we need!
                            using (var searcher = new PrincipalSearcher(userPrincipal)) {

                                // Find first user
                                var result = searcher.FindOne();

                                // We need the samAccountName, what we use to query AD for the furnished password
                                if (result != null) {
                                    var de = result.GetUnderlyingObject() as DirectoryEntry;
                                    samAccountName = de.Properties["samAccountName"].Value as string;
                                    firstName = de.Properties["givenName"].Value as string;
                                    lastName = de.Properties["sn"].Value as string;
                                }
                            }
                        }
                    }
                } else if (!string.IsNullOrEmpty(name)) {

                    // Remove the leading yourdomain\, if that has been furnished
                    if (name.StartsWith("$$YOUR_DOMAIN$$\\")) {
                        samAccountName = name.Substring("$$YOUR_DOMAIN$$".Length);
                    } else {
                        samAccountName = name;
                    }

                    // Construct context to query your Active Directory
                    using (var context = new System.DirectoryServices.AccountManagement.PrincipalContext(ContextType.Domain, "$$YOUR_AD_DOMAIN$$")) {

                        // Construct UserPrincipal object for this context, with samAccountName
                        var userPrincipal = new UserPrincipal(context);
                        userPrincipal.SamAccountName = samAccountName;

                        // Search and find the user for our samAccountName, so we retrieve the email address
                        using (var searcher = new PrincipalSearcher(userPrincipal)) {

                            // Find first user
                            var result = searcher.FindOne();

                            // Valid samAccountName, find email address
                            if (result != null) {
                                var de = result.GetUnderlyingObject() as DirectoryEntry;
                                email = de.Properties["mail"].Value as string;
                                firstName = de.Properties["givenName"].Value as string;
                                lastName = de.Properties["sn"].Value as string;
                            }
                        }
                    }
                }

                // We have a valid samAccountName and email address
                if ((!string.IsNullOrEmpty(samAccountName)) && (!string.IsNullOrEmpty(email))) {

                    // Query via samAccountName and password to verify user login
                    var entry = new DirectoryEntry("LDAP://$$YOUR_AD_DOMAIN$$", string.Format("$$YOUR_DOMAIN$$\\{0}", samAccountName), password);

                    try {
                        // Bind to the native AdsObject to force authentication.
                        var obj = entry.NativeObject;

                        var search = new DirectorySearcher(entry);

                        search.Filter = string.Format("(SAMAccountName={0})", samAccountName);
                        search.PropertiesToLoad.Add("cn");
                        var result = search.FindOne();

                        // We verified that this user exists, false otherwise
                        if (null == result) {
                            responseFlag = false;
                        } else {
                            responseFlag = true;
                        }
                    } catch (Exception) {
                        // Wrong password
                        responseFlag = false;
                    }
                }

                if (firstName == null) {
                    firstName = string.Empty;
                } else if (firstName.IndexOf('\"') >= 0) {
                    firstName = firstName.Replace("\"", "\\\"");
                }

                if (lastName == null) {
                    lastName = string.Empty;
                } else if (lastName.IndexOf('\"') >= 0) {
                    lastName = lastName.Replace("\"", "\\\"");
                }

                if (samAccountName == null) {
                    samAccountName = string.Empty;
                } else if (samAccountName.IndexOf('\"') >= 0) {
                    samAccountName = samAccountName.Replace("\"", "\\\"");
                }

                if (email == null) {
                    email = string.Empty;
                } else if (email.IndexOf('\"') >= 0) {
                    email = email.Replace("\"", "\\\"");
                }

                var builder = new StringBuilder("{\"user\":{\"firstName\":\"");
                builder.Append(firstName);
                builder.Append("\",\"lastName\":\"");
                builder.Append(lastName);
                builder.Append("\",\"email\":\"");
                builder.Append(email);
                builder.Append("\",\"samAccountName\":\"");
                builder.Append(samAccountName);
                builder.Append("\",\"authenticated\":");
                builder.Append(responseFlag ? 1 : 0);
                builder.Append("}}");

                responseString = builder.ToString();
                builder.Remove(0, builder.Length);
            }
        }

        Response.Write(responseString);
    }
</script>
