# contao-alt-editor-bundle

This bundle provides an easy way to see which images do not have an alt text and allows you to add them easily.

<img width="1351" alt="image" src="https://github.com/user-attachments/assets/d92bb12c-4ef3-40b7-a1c0-36074902d01d" />\
 \
Files that are missing an alt text in at least one language are also marked with an icon in the file manager.

![image](https://github.com/user-attachments/assets/0c7d4104-a708-4d88-bfbc-45f5e4ebbef4)

## File Usage integration

This bundle supports the [`inspiredminds/contao-file-usage`](https://extensions.contao.org/?p=inspiredminds/contao-file-usage) Contao extension, which allows you to see if an image is in use or not.

If the extension is installed, the images in the alt text editor are highlighted in red if they are in use according to the file usage extension.\
If they are not in use, they are highlighted in yellow. There is also a button to display the file usage of the image, just like in the file manager.

Keep in mind that the file usage extension may not be 100% accurate, so you should always double-check if the image is actually in use or not!