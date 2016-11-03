import os;

emailDomain = "";     ''' This is something like @acmecompany.com '''
adDomain = "";        ''' This is something like acme.acmecompany.org, how your AD is set up internally at the domain controller '''
domain = "";          ''' This is what people use to log in to Outlook Web Access for example - should be "acme" for acme\johndoe '''

with open("Default.aspx.out", "wt") as fout:
    with open("Default.aspx", "rt") as fin:
        for line in fin:

            updatedLine = line;

            if ("$$YOUR_EMAIL_DOMAIN$$" in line)     : updatedLine = updatedLine.replace("$$YOUR_EMAIL_DOMAIN$$", emailDomain);
            if ("$$YOUR_AD_DOMAIN$$" in line)        : updatedLine = updatedLine.replace("$$YOUR_AD_DOMAIN$$", adDomain);
            if ("$$YOUR_DOMAIN$$" in line)           : updatedLine = updatedLine.replace("$$YOUR_DOMAIN$$", domain);

            fout.write(updatedLine);
			
os.remove("Default.aspx");
os.rename("Default.aspx.out", "Default.aspx");
print("Complete.\n");
