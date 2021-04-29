-------------------------------------------------------------------------
-            Python Sample for Alexa Top Sites Service                  -
-------------------------------------------------------------------------
This sample will make a request to Alexa Top Sites
using the API user credentials and API plan key.

Tested with Python v3.7.2 and Python v2.7.14

1. Subscribe to Alexa Top Sites at https://aws.amazon.com/marketplace.
   (Note that you must have an AWS account with a valid credit card)
2. Register or login to the Alexa Developer Portal and copy the API Key shown on the page.
3. Uncompress the zip file into a working directory.
4. Install requirements:

pip install -r requirements.txt

5. Run:

python topsites.py --key=<API_KEY> --action=TopSites --options="&count=5&Output=json&ResponseGroup=Country" --Country=US

If you are getting "Not Authorized" messages, you probably have one of the
following problems:

1. Your API registration might not be complete. Return to the AWS Marketplace listing
page and confirm you are subscribed to the product.

2. If you are getting "Request Expired" messages, please check that the date
and time are properly set on your computer.

Copyright and License
All content in this repository, unless otherwise stated, is Copyright © 2019 Amazon.com, Inc. or its affiliates. All Rights Reserved.

Except where otherwise noted, all examples in this collection are licensed under the MIT license. The full license text is provided in the LICENSE file accompanying this repository.
