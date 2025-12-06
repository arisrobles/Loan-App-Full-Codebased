/**
 * Socket.io Client for Admin Panel
 * Handles real-time updates for support messages
 */

(function() {
    'use strict';

    let socket = null;
    let isConnected = false;
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5;

    /**
     * Initialize Socket.io connection
     */
    async function initSocket() {
        if (!window.SOCKET_CONFIG) {
            console.warn('Socket.io config not found');
            return;
        }

        try {
            // Get authentication token
            const tokenResponse = await fetch(window.SOCKET_CONFIG.getTokenUrl);
            if (!tokenResponse.ok) {
                console.error('Failed to get Socket.io token');
                return;
            }

            const { token } = await tokenResponse.json();
            if (!token) {
                console.error('No token received');
                return;
            }

            // Connect to Socket.io server
            const socketUrl = window.SOCKET_CONFIG.url || 'http://localhost:8080';
            socket = io(socketUrl, {
                auth: {
                    token: token
                },
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionDelay: 1000,
                reconnectionAttempts: maxReconnectAttempts
            });

            // Connection events
            socket.on('connect', () => {
                console.log('✅ Socket.io connected (Admin)');
                isConnected = true;
                reconnectAttempts = 0;
                updateConnectionStatus(true);
                
                // Join admin room
                socket.emit('join_admin_room');
            });

            socket.on('disconnect', () => {
                console.log('❌ Socket.io disconnected (Admin)');
                isConnected = false;
                updateConnectionStatus(false);
            });

            socket.on('connect_error', (error) => {
                console.error('Socket.io connection error:', error);
                reconnectAttempts++;
                if (reconnectAttempts >= maxReconnectAttempts) {
                    console.error('Max reconnection attempts reached');
                }
            });

            // Listen for new support messages
            socket.on('support_message_created', (data) => {
                console.log('New support message received:', data);
                handleNewSupportMessage(data);
            });

            // Listen for support message updates
            socket.on('support_message_updated', (data) => {
                console.log('Support message updated:', data);
                handleSupportMessageUpdate(data);
            });

        } catch (error) {
            console.error('Error initializing Socket.io:', error);
        }
    }

    /**
     * Update connection status indicator
     */
    function updateConnectionStatus(connected) {
        // Add/remove connection indicator in header if needed
        const statusIndicator = document.getElementById('socket-status');
        if (statusIndicator) {
            statusIndicator.textContent = connected ? '● Live' : '○ Offline';
            statusIndicator.className = connected 
                ? 'text-green-500 text-xs' 
                : 'text-gray-400 text-xs';
        }
    }

    /**
     * Handle new support message
     */
    function handleNewSupportMessage(data) {
        // Update badge count in header
        updateSupportMessageBadge();
        
        // Show notification
        showNotification('New Support Message', `${data.borrower?.fullName || 'User'} sent: ${data.subject}`, () => {
            window.location.href = `/admin/support-messages/${data.id}`;
        });

        // If on support messages index page, refresh the list
        if (window.location.pathname.includes('/support-messages') && 
            !window.location.pathname.match(/\/support-messages\/\d+$/)) {
            // Refresh the page or update the list via AJAX
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    }

    /**
     * Handle support message update
     */
    function handleSupportMessageUpdate(data) {
        // Update badge count
        updateSupportMessageBadge();
        
        // If viewing the specific message, refresh it
        const currentPath = window.location.pathname;
        if (currentPath.includes(`/support-messages/${data.id}`)) {
            // Refresh the page to show updated response
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    }

    /**
     * Update support message badge count
     */
    async function updateSupportMessageBadge() {
        try {
            // Fetch updated count from API endpoint
            const response = await fetch('/admin/support-messages');
            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Find the pending count from stats section
                const statsSection = doc.querySelector('.grid.grid-cols-1.md\\:grid-cols-5');
                if (statsSection) {
                    const pendingStat = statsSection.children[1]; // Second stat card (Pending)
                    if (pendingStat) {
                        const pendingCount = parseInt(pendingStat.querySelector('.text-2xl')?.textContent || '0');
                        
                        // Update badge in header
                        const badge = document.querySelector('a[href*="support-messages"] .absolute');
                        const badgeParent = document.querySelector('a[href*="support-messages"]');
                        
                        if (badgeParent) {
                            // Remove existing badge
                            const existingBadge = badgeParent.querySelector('.absolute');
                            if (existingBadge) {
                                existingBadge.remove();
                            }
                            
                            // Add new badge if count > 0
                            if (pendingCount > 0) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'absolute top-1 right-1 h-4 w-4 bg-yellow-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center';
                                newBadge.textContent = pendingCount > 9 ? '9+' : pendingCount.toString();
                                badgeParent.appendChild(newBadge);
                            }
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error updating badge:', error);
        }
    }

    /**
     * Show browser notification
     */
    function showNotification(title, message, onClick) {
        // Check if browser supports notifications
        if (!('Notification' in window)) {
            console.log('Browser does not support notifications');
            return;
        }

        // Request permission if needed
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    createNotification(title, message, onClick);
                }
            });
        } else if (Notification.permission === 'granted') {
            createNotification(title, message, onClick);
        }
    }

    /**
     * Create browser notification
     */
    function createNotification(title, message, onClick) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico'
        });

        notification.onclick = () => {
            window.focus();
            if (onClick) onClick();
            notification.close();
        };

        // Auto close after 5 seconds
        setTimeout(() => {
            notification.close();
        }, 5000);
    }

    /**
     * Join a specific support message room
     */
    function joinSupportMessageRoom(messageId) {
        if (socket && isConnected) {
            socket.emit('join_support_message', messageId.toString());
        }
    }

    /**
     * Leave a support message room
     */
    function leaveSupportMessageRoom(messageId) {
        if (socket && isConnected) {
            socket.emit('leave_support_message', messageId.toString());
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSocket);
    } else {
        initSocket();
    }

    // Expose functions globally for use in pages
    window.SocketAdmin = {
        joinSupportMessageRoom,
        leaveSupportMessageRoom,
        isConnected: () => isConnected
    };

})();

