import javax.swing.*;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.sql.*;

public class dashboard extends JFrame {
    
    public dashboard() {
        initComponents();
        setupBackground();
    }
    
    private void initComponents() {
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setTitle("Dashboard");
        setSize(1200, 800);
        setLocationRelativeTo(null);
        
        // Main container with background
        JPanel mainPanel = new JPanel(new BorderLayout()) {
            @Override
            protected void paintComponent(Graphics g) {
                super.paintComponent(g);
                Graphics2D g2d = (Graphics2D) g.create();
                
                // Create gradient background
                GradientPaint gradient = new GradientPaint(
                    0, 0, new Color(240, 248, 255),  // Light blue
                    getWidth(), getHeight(), new Color(230, 240, 250)  // Lighter blue
                );
                g2d.setPaint(gradient);
                g2d.fillRect(0, 0, getWidth(), getHeight());
                
                // Add subtle pattern overlay
                g2d.setColor(new Color(255, 255, 255, 20));
                for (int i = 0; i < getWidth(); i += 50) {
                    for (int j = 0; j < getHeight(); j += 50) {
                        g2d.fillOval(i, j, 2, 2);
                    }
                }
                
                g2d.dispose();
            }
        };
        
        // Left navigation panel
        JPanel leftPanel = new JPanel() {
            @Override
            protected void paintComponent(Graphics g) {
                super.paintComponent(g);
                Graphics2D g2d = (Graphics2D) g.create();
                
                // Magenta gradient background
                GradientPaint gradient = new GradientPaint(
                    0, 0, new Color(186, 85, 211),  // Magenta
                    getWidth(), getHeight(), new Color(147, 51, 234)  // Darker purple
                );
                g2d.setPaint(gradient);
                g2d.fillRect(0, 0, getWidth(), getHeight());
                
                g2d.dispose();
            }
        };
        leftPanel.setPreferredSize(new Dimension(250, 0));
        leftPanel.setLayout(new BoxLayout(leftPanel, BoxLayout.Y_AXIS));
        leftPanel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        
        // Title
        JLabel titleLabel = new JLabel("Dashboard");
        titleLabel.setFont(new Font("Segoe UI", Font.BOLD, 24));
        titleLabel.setForeground(Color.WHITE);
        titleLabel.setAlignmentX(Component.CENTER_ALIGNMENT);
        titleLabel.setBorder(BorderFactory.createEmptyBorder(0, 0, 30, 0));
        leftPanel.add(titleLabel);
        
        // Navigation buttons
        String[] menuItems = {"Dashboard", "Customer", "Products", "Orders", "Reports", "Manage Staff"};
        for (String item : menuItems) {
            JButton btn = createNavButton(item);
            btn.setAlignmentX(Component.CENTER_ALIGNMENT);
            leftPanel.add(btn);
            leftPanel.add(Box.createVerticalStrut(10));
        }
        
        // Logout button
        leftPanel.add(Box.createVerticalGlue());
        JButton logoutBtn = createNavButton("LOG OUT");
        logoutBtn.setAlignmentX(Component.CENTER_ALIGNMENT);
        logoutBtn.setBackground(new Color(220, 53, 69)); // Red for logout
        logoutBtn.setForeground(Color.WHITE);
        leftPanel.add(logoutBtn);
        
        // Right content panel
        JPanel rightPanel = new JPanel(new BorderLayout());
        rightPanel.setOpaque(false);
        
        // Welcome content + rating panel
        JPanel contentPanel = new JPanel(new BorderLayout());
        contentPanel.setOpaque(false);

        JPanel headerPanel = new JPanel(new GridBagLayout());
        headerPanel.setOpaque(false);
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(20, 20, 20, 20);

        JLabel logoLabel = new JLabel("KUSEY");
        logoLabel.setFont(new Font("Arial", Font.BOLD, 72));
        logoLabel.setForeground(new Color(50, 50, 50));
        gbc.gridx = 0;
        gbc.gridy = 0;
        headerPanel.add(logoLabel, gbc);

        JLabel subLabel = new JLabel("APPAREL");
        subLabel.setFont(new Font("Arial", Font.PLAIN, 24));
        subLabel.setForeground(new Color(100, 100, 100));
        gbc.gridy = 1;
        headerPanel.add(subLabel, gbc);

        JLabel welcomeLabel = new JLabel("Welcome to Your Dashboard");
        welcomeLabel.setFont(new Font("Segoe UI", Font.PLAIN, 28));
        welcomeLabel.setForeground(new Color(70, 70, 70));
        gbc.gridy = 2;
        gbc.insets = new Insets(40, 20, 20, 20);
        headerPanel.add(welcomeLabel, gbc);

        contentPanel.add(headerPanel, BorderLayout.NORTH);
        contentPanel.add(createRatingPanel(), BorderLayout.CENTER);

        rightPanel.add(contentPanel, BorderLayout.CENTER);
        
        // Add panels to main panel
        mainPanel.add(leftPanel, BorderLayout.WEST);
        mainPanel.add(rightPanel, BorderLayout.CENTER);
        
        setContentPane(mainPanel);
    }
    
    private JButton createNavButton(String text) {
        JButton button = new JButton(text) {
            @Override
            protected void paintComponent(Graphics g) {
                if (getModel().isPressed()) {
                    g.setColor(new Color(255, 255, 255, 100));
                } else if (getModel().isRollover()) {
                    g.setColor(new Color(255, 255, 255, 150));
                } else {
                    g.setColor(new Color(255, 255, 255, 50));
                }
                g.fillRoundRect(0, 0, getWidth(), getHeight(), 10, 10);
                super.paintComponent(g);
            }
        };
        
        button.setFont(new Font("Segoe UI", Font.PLAIN, 14));
        button.setForeground(Color.WHITE);
        button.setPreferredSize(new Dimension(200, 45));
        button.setMaximumSize(new Dimension(200, 45));
        button.setMinimumSize(new Dimension(200, 45));
        button.setBorder(BorderFactory.createLineBorder(Color.WHITE, 2, true));
        button.setContentAreaFilled(false);
        button.setFocusPainted(false);
        button.setCursor(new Cursor(Cursor.HAND_CURSOR));
        
        button.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                // Handle button clicks
                JOptionPane.showMessageDialog(null, text + " clicked!");
            }
        });
        
        return button;
    }

    // 5-star rating panel that saves to MySQL `ratings` table
    private JPanel createRatingPanel() {
        JPanel panel = new JPanel();
        panel.setOpaque(false);
        panel.setBorder(BorderFactory.createEmptyBorder(40, 40, 40, 40));
        panel.setLayout(new BoxLayout(panel, BoxLayout.Y_AXIS));

        JLabel title = new JLabel("Rate your experience");
        title.setAlignmentX(Component.CENTER_ALIGNMENT);
        title.setFont(new Font("Segoe UI", Font.BOLD, 20));
        title.setForeground(new Color(70, 70, 70));

        JLabel subtitle = new JLabel("Click a star from 1–5");
        subtitle.setAlignmentX(Component.CENTER_ALIGNMENT);
        subtitle.setForeground(new Color(120, 120, 120));

        JPanel starsPanel = new JPanel();
        starsPanel.setOpaque(false);
        starsPanel.setLayout(new FlowLayout(FlowLayout.CENTER, 10, 20));

        JButton[] starButtons = new JButton[5];

        for (int i = 0; i < 5; i++) {
            final int rating = i + 1;
            JButton star = new JButton("☆");
            star.setFont(new Font("Segoe UI", Font.PLAIN, 32));
            star.setBorderPainted(false);
            star.setContentAreaFilled(false);
            star.setFocusPainted(false);
            star.setCursor(new Cursor(Cursor.HAND_CURSOR));

            star.addActionListener(e -> {
                // Update star visuals
                for (int j = 0; j < 5; j++) {
                    starButtons[j].setText(j < rating ? "★" : "☆");
                    starButtons[j].setForeground(j < rating ? new Color(255, 193, 7) : new Color(180, 180, 180));
                }

                // Save to DB
                saveRatingToDatabase(rating);
            });

            starButtons[i] = star;
            starsPanel.add(star);
        }

        panel.add(title);
        panel.add(Box.createVerticalStrut(5));
        panel.add(subtitle);
        panel.add(starsPanel);

        return panel;
    }

    // JDBC save to MySQL `bookingphp` database, `ratings` table
    private void saveRatingToDatabase(int rating) {
        String url = "jdbc:mysql://localhost:3306/bookingphp";
        String user = "root";
        String password = "";

        String sql = "INSERT INTO ratings (user_id, source, rating, comment) VALUES (NULL, 'java', ?, NULL)";

        try (Connection conn = DriverManager.getConnection(url, user, password);
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, rating);
            ps.executeUpdate();
            JOptionPane.showMessageDialog(this, "Thank you! Your " + rating + "-star rating has been saved.");

        } catch (SQLException ex) {
            ex.printStackTrace();
            JOptionPane.showMessageDialog(this, "Error saving rating: " + ex.getMessage(), "Database Error",
                    JOptionPane.ERROR_MESSAGE);
        }
    }
    
    private void setupBackground() {
        // Set frame background
        getContentPane().setBackground(Color.WHITE);
    }
    
    public static void main(String[] args) {
        // Set look and feel
        try {
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeel());
        } catch (Exception e) {
            e.printStackTrace();
        }
        
        SwingUtilities.invokeLater(new Runnable() {
            @Override
            public void run() {
                new dashboard().setVisible(true);
            }
        });
    }
}

