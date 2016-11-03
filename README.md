# ADAuthentication
Simple HTTPS Web Service to authenticate users against the AD via post - usage: name=username_or_emailaddress&password=yourpasswordinAD

* You have a plain Windows Server 2012 R2 Standard in your corporate network (internal) that you want to use for AD Authentication for any of your applications installed inside your network.

# Install IIS

* Bring up Server Manager, navigate to dashboard
* Click on 2 - Add roles and features
* On the Before you begin screen - Click Next
* Installation Type - Choose Role-based or feature-based installation
* Server Selection: Select destination server - from the options shown, select your current server where you need to install and configure IIS
* Server Roles: Select Web Server (IIS)
* A new box pops out. Add Roles and Features Wizard: Web Server (IIS) - Management Tools - [Tools] IIS Management Console. You are suggested to include this installation.
* Verify that the "Include Management Tools (if applicable)" checkbox is checked. Select "Add Features"
* Click Next > to start the installation and configuration
* The Features options opens out with the Select Features header.
* Expand the .NET Framework 4.5 Features (2 of 7 installed) option in the list shown
* Check and select ASP.NET 4.5 shown in this list.
* Expand the WCF Services under the .NET Framework 4.5 Features option
* Select HTTP Activation
* The Add Roles and Features Wizard pops out once again, informing that a lot of additional features need to be included to enable HTTP Activation, like ISAPI Extension, Filters and .NET Extensibility 4.5. Click "Add Features"
* Click Next in the main window.
* The Web Server Role (IIS) option explains how Role Services work. Press Next >
* In the Role Services (Select role services) option list, select HTTP Redirection under Web Server, select Windows Authentication under Security, click Next.
* In the final Confirm installation selections screen, all your options are displayed. Check and select the option on top that states, Restart the destination server automatically if required.
* A confirmation box pops out to confirm this selection. Choose Yes.
* Click Install
* The installation progress screen opens out with a progress bar.
* You get a notification on the Feature Installation with an installation succeeded message. Click on Close on this window.
* Type http://localhost/ and verify that the default IIS page opens out.

Install Microsoft Visual Studio 2015 Community Edition with all the default components, restart after the operation finishes.

# Creating the project

* Create a folder C:\services
* Create a folder: C:\services\ADAuth
* Launch Visual Studio Community 2015
* Click on New Project...
* The New Project box that opens out, on the left pane - expand Templates > Visual C# > Windows > Web, and choose ASP.NET Web Application
* The Name of the Application: ADAuthentication
* Location: C:\services\ADAuthentication
* Uncheck the Create directory for solution check-box
* Verify that the Add to Source Control box is unchecked for now
* Also uncheck the Add Application Insights to Project option on the right pane. We will ignore this for now.
* Click OK
* You get presented with a "Select a template:" option in the Wizard. Choose Empty from the ASP.NET 4.5.2 Templates section.
* Uncheck the Microsoft Azure: Host in the cloud option. We are doing everything local here.
* Leave all the other options unchecked.
* Click on OK
* Microsoft Visual Studio creates the ADAuth project and presents you with an empty Developer Environment Window.
* Right click on the ADAuth project under the ADAuth Solution in the right Solution Explorer pane, and choose Add > New Item
* In the New Item popup applet window, verify that you are presented with Installed > Visual C# > Web, and choose Web Form, the fourth option from the top
* Name your new page Default.aspx
* Click on Add.
* Visual Studio creates a barebones page and presents that to you.
* Inside the <div></div> section on line 12, enter the text: &lt;h1&gt;hello,world&lt;/h1&gt;
* Save the page.
* Build the solution by selecting Build from the top menu, and clicking on the option Build Solution.
* Verify that everything builds perfectly.
* Run the application in the sandbox web server by selecting Debug from the top menu, and clicking on Start Debugging
* Your default web browser opens out with a page that has a URL that may be similar to http://localhost:60890/Default.aspx except for the random free TCP port chosen on your Windows instance.
* This should display the string hello,world in bold, as a headline.

Now let's configure this page to be served over HTTPS via a self-signed certificate and the default website.

* Bring up IIS Manager by pressing Windows Key-R, and entering inetmgr
* IIS Manager opens out and it may present you with a dialog that states, "Do you want to get started with Microsoft Web Platform to stay connected with the latest Web Platform Components?", choose No for now. Do not click on the "Do not show this message." option, we will consider this at a later point in time.
* In the connections pane on the left, expand your box name, like SERVERNAME001 that shows you Application Pools and Sites under it.
* Expand the Sites node of the tree, that lists Default Web Site.
* Click on the Default Web Site.
* From the right Actions pane, click Basic Settings to open the Edit Site dialog
* The Physical Path is set by default to %systemdrive%\inetpub\wwwroot. We need to change this to point to our ADAuthentication app on this box.
* Click on the button with the three dots next to it. This should give you the Folder Selection dialog. Choose c:\services\ADAuthentication\ADAuthentication in it.
* Click on OK in the Folder Selection dialog.
* Click on OK in the Edit Site dialog.
* Now, navigate back to your default browser. Hitting http://localhost/Default.aspx should open out your page with the hello,world message.
* Go back to Visual Studio and select Debug > Stop Debugging. We don't need to use the debugger anymore.

# Next step is to secure our website over HTTPS, so it does not accept or serve anything plaintext.

* We will also use an Application Pool that is configured to run as a user with a service account in the Active Directory. This should not be a regular user with a password that changes every few weeks (4 or 12). We need a service account in the AD, that usually has a fairly complex password that doesn't expire.
* Please procure or obtain the credentials of a service account (login and password) in your AD. This could be named serviceaccount01.
* The reason we cannot run our website for AD Authentication with the default App Pool is that such accounts have no authority or privilege to query the AD.
* Let's assume you have an account named serviceaccount01 in your Active Directory GAL with a password that isn't public knowledge.
* First off, we will add this user to the Local User Group called IIS_IUSRS.
* Click on the Start button on your taskbar, choose Control Panel and choose Administrative Tools.
* Choose Computer Management in the set of options displayed for Control Panel > All Control Panel Items > Administrative Tools
* The Computer Management section that opens out has a left pane with a set of options listed under System Tools
* Choose Local Users and Groups and expand this node. You should see a folder for groups
* List out all the groups that are defined on this computer. You should see one for IIS_IUSRS
* Double click on IIS_USRS to list out the existing members, if there are any defined.
* We will add serviceaccount01 to this list. Click on the Add.. button under the list for the IIS_IUSRS properties screen
* This opens out the Select Users, Computers, Service Accounts, or Groups dialog applet.
* In the textbox for Enter the object names to select, enter serviceaccount01 so you can search the AD to get the DN (distinguishedName) for this account.
* Click on Check Names to the right to verify that this search suceeds.
* Click on OK at the bottom to dismiss this dialog
* Click on OK at the bottom of the IIS_IUSRS Properties dialog to dismiss that too.
* Close the Computer Management Window.
* Navigate back to the IIS Manager
* Click on Application Pools in the left Connections pane.
* This should list out the three default pools. You may have more if you've created some in the past.
* Right click on this window and select "Add Application Pool...". You have this option on the right Actions pane too.
* Let's name this pool serviceaccount01_Pool so we are clear about it's ownership and intentions.
* Use the .NET CLR Version .NET CLR Version v4.0.30319 that is latest at the time of this writing.
* Managed Pipeline Mode: Integrated, and leave the checkbox for "Start Application Pool immediately" checked.
* Now, choose the option Advanced Settings under the Edit Application Pool on the right Actions pane to change the identity.
* In the Advanced Settings window for this Application Pool, you have the Process Model heading with the second option Identity that is set by default to ApplicationPoolIdentity. Click on the button with the three period to the right of it to open the Application Pool Identity dialog.
* Choose Custom Account instead of the Built-in account, and click on the Set button to the right of it.
* Enter the User-name: yourdomain\serviceaccount01
* Type the password twice to confirm that it is indeed correct.
* Click on OK to dismiss this dialog
* Click on OK to dismiss the Advanced Settings dialog.
* We need to set our default website to run with this app-pool and run on HTTPS with port 443, not the default port-80 HTTP.
* Click on Default Website in the Connections Pane of IIS Manager
* Click on Bindings in the Right Actions Pane to open the Site Bindings dialog
* Click on the Add to the right.
* This brings up the Add Site Bindings dialog. We will make the following selections:
* Type: HTTPS, IP Address: All unassigned, Port: 443, Host-name: Leave empty, SSL Certificate: Select the default IIS Express Development Certificate that is installed by default on your machine. You would need a production-grade, blessed and baptized X.509 certificate if you want to move this service inside your network, so clients don't get shooed away via a self-signed certificate.
* Click on OK to dismiss this dialog.
* Now the site bindings lists two separate means to access your default website, port 80 and port 443. Delete the binding for port 80. We should not have this content accessible over plaintext
* Click on Close to dismiss this window.
* From the right Actions pane, click Basic Settings to open the Edit Site dialog
* Click on Select button on the top right, next to the default App Pool
* The Select Application Pool dialog opens out.
* Select the new AppPool we just created, named serviceaccount01_Pool.
* Click on OK in the Select Application Pool Catalog.
* Click on OK in the Edit Site dialog.
* Now, navigating to https://localhost/Default.aspx will present you with a warning window, complaining about a self-signed certificate whose authenticity cannot be verified.
* Google Chrome shows up with "Your connection is not private". Click on the ADVANCED link at the bottom and choose Proceed to localhost (unsafe).
* The same hello,world page opens out with content served over HTTPS. http://localhost/Default.aspx will no longer work.

* Now, let's navigate back to our Visual Studio window.
* Open the web.config file in the editor window.
* Line #8 is the compilation directive, that needs to be replaced to include references to System.DirectoryServices and System.DirectoryServices.AccountManagement.
* Replace the line

&lt;compilation debug="true" targetFramework="4.5.2"/&gt;

with 

&lt;compilation debug="true" targetFramework="4.5.2" &gt;
&lt;assemblies&gt;
&lt;add assembly="System.DirectoryServices, Version=4.0.0.0, Culture=neutral, PublicKeyToken=B03F5F7F11D50A3A"/&gt;
&lt;add assembly="System.DirectoryServices.AccountManagement, Version=4.0.0.0, Culture=neutral, PublicKeyToken=B77A5C561934E089"/&gt;
&lt;/assemblies&gt;
&lt;/compilation&gt;

* Save the file.
* Right click on References under the ASP.NET Project ADAuth in the Solution Explorer to open the Reference Manager - ADAuth window. Navigate to Assemblies > Framework in the left pane, and select System.DirectoryServices and System.DirectoryServices.AccountManagement in this list.

* Copy over the contents of file https://github.com/sundarnut/ADAuthentication/blob/master/Default.aspx over to your Default.aspx
* Replace $$YOUR_EMAIL_DOMAIN$$ with @yourcompany.com, $$YOUR_AD_DOMAIN$$ with acme.acmecompany.org (your AD namespace), $$YOUR_DOMAIN$$ with acme (if people login as acme\johndoe) across this file.
* Build your project to update the website.
* Your site is deployed and ready!

* Please replace the self-signed X.509 certificate with an authentic one, blessed and baptized by a Certificate-Authority, to probably map to either *.yourcompanyname.com or servername.internalnetwork.org on your internal network, before people actually use the website

# Test via PHP Page on LAMP

* Open 01.php from Github at https://github.com/sundarnut/ADAuthentication/blob/master/01.php in a text editor and edit line #73 to point to your box that should hopefully now have a legitimate X.509 digital certificate. 
* Save this file and copy over two files 01.php and main.css to a Linux box that should have Apache and PHP installed.
* Copy these files to a folder under /var/www/html of the Linux box, so you can summon 01.php via https://linuxboxname/01.php and verify that authentication indeed works in the AD.

# NEVER LOG ANY PASSWORD TO TEXT FILES, SYSLOG, SPLUNK OR TO DATABASE TABLES.
