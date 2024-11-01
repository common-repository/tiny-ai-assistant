=== Tiny AI Assistant ===
Contributors: monsz
Donate link: https://tiny-ai-assistant.aichatbot.hu/
Tags: ChatGPT, OpenAI, AI, GPT-4, Ai content writer, copywriting, Content Writing Assistant, Content Writer, TinyMCE
Requires PHP: 5.6
Requires at least: 5.0
Tested up to: 6.2.2
Stable tag: 1.1
License: GPLv2 or later

== Description ==

We have turbocharged the TinyMCE text editor, making it even easier and faster to produce texts. 
Our little plugin integrates into editor interfaces (all text editors on the web page editing surface), you write the command into the text box, click, and the desired text appears instantly. 
It couldn't be simpler or faster. 
You can save frequently used commands, so in the future, you will only need a single click!

How to use:
After installation, provide the API key you can generate on openai.com
Set up the commands (you can do this later too). In the free version, you can save 3 commands.

Premium version: 
The premium version offers more options: You can adjust how creative the text should be You can create as many commands as you like The model can be chosen

Coming soon: You will be able to use several different AI providers You can also determine the order of the commands

Supported plugins: 
   - Elementor 
   - ACF (Advanced Custom Fields)


== Installation ==

1. Upload the plugin folder to the WordPress `/wp-content/plugins/` directory.
2. Enable the plugin on the Installed Plugins page in the WordPress dashboard.
3. Open the Settings -> Tiny AI Assistant Settings subpage in the dashboard.
4. Set the OpenAI API key you can generate on openai.com
5. On the Tiny AI Assistant Settings page, you can add new commands to the available commands in the "Add New Command" section:
   a) Enter the command name (e.g., "Write poem").
   b) Enter the command to be sent to ChatGPT (e.g., "Write a poem with 8 lines on this topic:").
   c) Click the "+" button. The new command will appear in the "Active Commands" input field at the top.
   d) Click the "Save" button at the bottom of the Tiny AI Assistant Settings page to save the settings.
6. Previously added commands can be removed in the "Remove Commands" section as needed.
7. Set the response creativity: It can be adjusted between 0 and 1, where 0 generates the least creative (most predictable) results, and 1 produces the most creative (least predictable) results.
8. If you have subscribed to the service, enter your license key in the "License Key (for Premium subscription)" section.
9. It's important to click the "Save" button at the bottom of the Tiny AI Assistant Settings page to save any modifications made on the page.



== Usage ==

Once you have followed the instructions in the Installation section, including setting up the license key if applicable and configuring the desired commands,
the Tiny AI Assistant commands will appear in a dropdown list within the TinyMCE editor toolbar that appears anywhere in the WordPress interface. Next to it, there will be a button for undoing.
IMPORTANT!
You can find the dropdown list and undo button of the Tiny AI Assistant in the SECOND row of the TinyMCE editor toolbar, which is initially in a closed state. 
Therefore, you need to enable the appearance of the second row of the toolbar using the dedicated button to access the tools of the Tiny AI Assistant!

All you need to do is provide the relevant information for the command in the text editor interface associated with the respective editor toolbar. 
For example, if you use the "Write poem" command, logically, the system expects a topic. 
You would enter the topic in the text editor area, such as "Smurfs," select the "Write poem" option from the dropdown list, and after a short wait, the generated poem will appear instead of the "Smurfs" text.

If the generated content is not satisfactory, you can click the undo button next to the dropdown list to revert back to the content before the generation.

IMPORTANT!
Unused content from tokens will not be refunded, meaning that any command you issue to the system and receive a response for will consume the corresponding tokens regardless. Additionally, it's worth noting that both issuing a command and receiving the resulting content consume tokens. For example, if you translate a longer text with the system, translating one A4 page of text may consume tokens equivalent to two A4 pages of text due to the command issuance and the returned content.


== External Service Calls ==
The plugin utilizes our own checker API service (https://construct.pdk.hu/tinyAIEx-checker/checker_api.php) to retrieve the OpenAI license key to be used when using the plugin.
The checker API returns the OpenAI key based on the license key provided by the user in the plugin's settings, as long as there is an available token quota associated with the subscription.

Data Privacy Statement for the checker API:
No personal data is transmitted when using the API; only the Tiny AI Assistant license key, the name of the corresponding subscription package, and the number of tokens used or available are transmitted.



== Changelog ==
= 1.0 =
First release

= 1.0.1 =
Minor corrections in translations

= 1.1 =
- Changed the plugin from subscription model to free / premium model
- Included model selector


== Frequently Asked Questions ==

= How can I obtain an OpenAI API key to set up within the plugin?
1. Create an OpenAI account: Go to the OpenAI website and sign up for an account if you haven't already.
2. Navigate to the API section: Once logged in, go to your account settings or dashboard. Look for an option related to the API or developer tools.
3. Generate an API key: In the API section, you should find an option to generate an API key. Click on it and follow any prompts or instructions provided. This will generate a unique API key that you can use within your plugin.

= How can I buy the premium version of the plugin?
You can buy the license on our website: https://tiny-ai-assistant.aichatbot.hu/

= The plugin is not functioning, and I'm receiving an error message. What should I do? =
It is possible that the OpenAI system is overloaded. In such cases, it's advisable to wait for a while.
If the service does not recover within a short period, please inform us by emailing tiny-ai-assistant@aichatbot.hu or using the contact form on the plugin's website.

