package json;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;

import com.google.gson.Gson;

public class WebService {
	GetStatus status;
	URLConnection connection;
	String json;
	String wsPath = "http://mybudgetpal.com/ws/";
	
	
	private String getJsonFromUrl(String url) throws IOException
	{
		try {
			connection = new URL(wsPath + url).openConnection();
		} catch (Exception e) {
			e.printStackTrace();
		}
		BufferedReader in = new BufferedReader(new InputStreamReader(
                connection.getInputStream()));
		String json = "";
		String line;
		while ((line = in.readLine()) != null) {
			json += line.trim();
		}
		in.close();
		this.json = json;
		return json;
	}
	
	
	// Przykład metody zwracajacej liste obiektow
	public Budgets GetBudgets() throws IOException
	{
		String url = "server2.php?a=getbudgets";
		this.getJsonFromUrl(url);
		this.status = new GetStatus(this.json); 
		if (this.status.isSet())
			return null;
		else
			return new Gson().fromJson(this.json, Budgets.class);
	}
	
	// przyklad metody zwracajacej jedynie status
	// Zalogowuje, zwraca false w przypadku błędu, albo true jak sie uda
	public boolean Login(String user, String password) throws Exception
	{
		String url = "server.php?a=login&user="+user+"&password="+Utils.sha256(password);
		
		this.getJsonFromUrl(url);
		this.status = new GetStatus(this.json); 
		if (this.status.isError())
			return false;
		else
			return true;
	}
	
	// przyklad metody zwracajacej typ prosty
	public double GetBudgetBilans(int budgetId) throws Exception
	{
		String url = "server.php?a=getbudgetbilans&budgetId=" + budgetId;
		this.getJsonFromUrl(url);
		this.status = new GetStatus(this.json); 
		if (this.status.isSet())
			return -1;
		else
			return new Gson().fromJson(this.json, Double.class);
		}


}
