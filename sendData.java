package Orbs;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;

import javax.crypto.Cipher;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;

import org.dreambot.api.Client;
import org.dreambot.api.methods.MethodProvider;



public class sendData extends Thread {
	long orbs,xp,time;
	private Charger ctx;
	long profits;
	public sendData (final Charger ctx){

		this.ctx = ctx;
		//variables to send to the server
		this.orbs = ctx.orbsMade;
		this.xp = orbs*76;
		this.time = ctx.t.elapsed()/1000;
		this.profits = ctx.profits;
	}
	
	
	
	@Override
	public void run(){

	 // I did not use all the variables in my database so the last variable is 0, if you database has more than 5 variables you will need to include them all
	 sendSignatureData(time,orbs,profits,xp,0);	
	}
	
	public void sendSignatureData(long runtimeInSeconds, long var1, long var2, long var3, long var4) {

		// In order to provide some security, so that people will not tamper the data and post it themselves, we will be encrypting it here and decrypting it in php.
		// These keys should be the same as in PHP (db.php) (16 characters long)
		String privateKey = "";
		String initVector = "";

		try {
			
			// data we will be encrypting. you can remove the var's if you want (username and runtime are required though)

		        //Change Table_Name to the SQL table name you want to upate
			String data = initVector+","+"Table_Name"+","+ Client.getForumUser().getUsername()+","+runtimeInSeconds+","+var1+","+var2+","+var3+","+var4; // comma delimited so we can split in php

			// set up iv and key for encrypting
			IvParameterSpec ivspec = new IvParameterSpec(initVector.getBytes());
			SecretKeySpec keyspec = new SecretKeySpec(privateKey.getBytes(), "AES");
			Cipher cipher = Cipher.getInstance("AES/CBC/PKCS5Padding");

			// encrypt
			cipher.init(Cipher.ENCRYPT_MODE, keyspec, ivspec);
			byte[] encrypted = cipher.doFinal(data.getBytes("UTF-8"));
			
			// convert to hex
			String token = "";
			for (int i = 0; i < encrypted.length; i++) {
				if ((encrypted[i] & 0xFF) < 16) {
					token = token + "0" + java.lang.Integer.toHexString(encrypted[i] & 0xFF);
				} else {
					token = token + java.lang.Integer.toHexString(encrypted[i] & 0xFF);
				}
			}

			// And post it :)
			
			URL url = new URL("http://nonedreambot.x10host.com/input.php?token="+token);
			URLConnection conn = url.openConnection();

			// fake request coming from browser (solves permission issue on shared webhosting)
			conn.setRequestProperty("User-Agent","Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13 (.NET CLR 3.5.30729)");

			BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
			in.readLine();
			in.close();
			
			//If sending data more than once during the script duration we need to reset our variables

			ctx.orbsMade =0;
			ctx.t.reset();
			
			//---------------		
			
		} catch (Exception e) {
			
			MethodProvider.log(e.getMessage());
		}
		
	}
}
