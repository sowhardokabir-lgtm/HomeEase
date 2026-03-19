import java.sql.Connection;
import java.util.ArrayList;
import java.util.List;

/**
 * Singleton Pattern: HomeEaseDbController
 * This single class manages all database interactions for the entire platform.
 */
public class HomeEaseDbController {

    // 1. The volatile instance ensures changes are visible across all threads
    private static volatile HomeEaseDbController instance;
    
    // Simulated fields for your website's data
    private String connectionStatus;
    private List<String> systemLogs;

    // 2. Private Constructor: Prevents other classes from using 'new'
    private HomeEaseDbController() {
        this.connectionStatus = "CONNECTED_TO_HOMEEASE_SQL";
        this.systemLogs = new ArrayList<>();
        System.out.println(">>> [SYSTEM] HomeEase Database Controller Initialized.");
    }

    // 3. Global Access Point: Thread-safe Double-Checked Locking
    public static HomeEaseDbController getInstance() {
        if (instance == null) { // First check (no locking)
            synchronized (HomeEaseDbController.class) {
                if (instance == null) { // Second check (with locking)
                    instance = new HomeEaseDbController();
                }
            }
        }
        return instance;
    }

    // --- Business Logic Methods ---

    public void logActivity(String user, String action) {
        String entry = String.format("[LOG] User: %s | Action: %s", user, action);
        systemLogs.add(entry);
        System.out.println(entry);
    }

    public void processBookingUpdate(int bookingId, String status) {
        System.out.println(">>> [DB UPDATE] Booking #" + bookingId + " changed to: " + status);
        logActivity("SYSTEM", "Booking Status Update: " + bookingId);
    }

    public void showAllLogs() {
        System.out.println("\n--- Platform Activity History ---");
        for (String log : systemLogs) {
            System.out.println(log);
        }
    }

    public String getConnectionStatus() {
        return connectionStatus;
    }

    // --- Simulation of usage in your project ---
    public static void main(String[] args) {
        // Even if we try to "get" the instance twice, it's the same object
        HomeEaseDbController sessionA = HomeEaseDbController.getInstance();
        HomeEaseDbController sessionB = HomeEaseDbController.getInstance();

        // Check if they are the same
        System.out.println("Are both sessions identical? " + (sessionA == sessionB));

        // Using the singleton to log actions from your LOGIN.html and ADMIN.html logic
        sessionA.logActivity("Admin_User", "Accessed Provider Dashboard");
        sessionB.logActivity("Provider_01", "Updated AC Repair Price to 1500");
        
        sessionA.processBookingUpdate(101, "COMPLETED");

        // Display everything stored in the single instance
        sessionA.showAllLogs();
    }
}
