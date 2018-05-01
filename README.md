# AudioWatermarkDemo

Use LSB algorithm to insert secret message to audio file (*.WAV)

!!! IMPORTANT !!!

	* PHP version is 7. And you need to change PHP's config in "php.ini"
		upload_max_filesize = 50M
		post_max_size=50M
	when run.

	* Format of *.WAV file need to have Header, Subchunk1, Subchunk2, Subchunk3. Subchunk2 (SubchunkID = "LIST") is optional but I need it!!!

	* All audio files, which is uploaded to and downloaded from, are stored in my Google Drive (I used Google Drive API). You can change to your Google Drive by changing file called "service_account_keys.json" to your Service Account Keys JSON.

	* Don't remove the folder named "music" because I use it to store temporary audio files.
		
!!! IMPORTANT !!!

!!! CONTACT ME !!!
	
	Facebook:	https://www.facebook.com/fvoxuankhang
	Email:		khangvoxuan@gmail.com
	SĐT:		(+84) 162 936 2887

!!! CONTACT ME !!!