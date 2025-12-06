import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { io, Socket } from 'socket.io-client';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Constants from 'expo-constants';

interface SocketContextType {
  socket: Socket | null;
  isConnected: boolean;
  connect: () => void;
  disconnect: () => void;
}

const SocketContext = createContext<SocketContextType>({
  socket: null,
  isConnected: false,
  connect: () => {},
  disconnect: () => {},
});

export const useSocket = () => useContext(SocketContext);

interface SocketProviderProps {
  children: ReactNode;
}

// Get API base URL from config or environment
const getApiBaseUrl = async () => {
  try {
    // Try to get from AsyncStorage first (user might have configured it)
    const storedUrl = await AsyncStorage.getItem('api_base_url');
    if (storedUrl && storedUrl.startsWith('http')) {
      return storedUrl.replace('/api/v1', '').replace('/api', ''); // Remove API path
    }
    
    // Try to extract IP from Expo debugger URL
    const sources = [
      Constants.expoConfig?.hostUri,
      Constants.expoConfig?.extra?.debuggerHost,
      Constants.manifest?.debuggerHost,
      Constants.manifest2?.extra?.expoGo?.debuggerHost,
    ];

    for (const source of sources) {
      if (!source) continue;
      const match = source.match(/(\d+\.\d+\.\d+\.\d+)/);
      if (match && match[1] && match[1] !== '127.0.0.1') {
        return `http://${match[1]}:8080`;
      }
    }
    
    // Fallback
    return 'http://localhost:8080';
  } catch (error) {
    console.error('Error getting API base URL:', error);
    return 'http://localhost:8080';
  }
};

export const SocketProvider: React.FC<SocketProviderProps> = ({ children }) => {
  const [socket, setSocket] = useState<Socket | null>(null);
  const [isConnected, setIsConnected] = useState(false);

  const connect = async () => {
    try {
      const token = await AsyncStorage.getItem('authToken');
      if (!token) {
        console.log('No auth token, skipping socket connection');
        return;
      }

      const baseUrl = await getApiBaseUrl();
      const socketUrl = baseUrl.startsWith('http') ? baseUrl : `http://${baseUrl}`;

      console.log('Connecting to Socket.io server:', socketUrl);

      const newSocket = io(socketUrl, {
        auth: {
          token: token,
        },
        transports: ['websocket', 'polling'],
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionAttempts: 5,
      });

      newSocket.on('connect', () => {
        console.log('✅ Socket.io connected');
        setIsConnected(true);
      });

      newSocket.on('disconnect', () => {
        console.log('❌ Socket.io disconnected');
        setIsConnected(false);
      });

      newSocket.on('connect_error', (error) => {
        console.error('Socket.io connection error:', error);
        setIsConnected(false);
      });

      setSocket(newSocket);
    } catch (error) {
      console.error('Error connecting to Socket.io:', error);
    }
  };

  const disconnect = () => {
    if (socket) {
      socket.disconnect();
      setSocket(null);
      setIsConnected(false);
    }
  };

  useEffect(() => {
    connect();

    return () => {
      disconnect();
    };
  }, []);

  return (
    <SocketContext.Provider value={{ socket, isConnected, connect, disconnect }}>
      {children}
    </SocketContext.Provider>
  );
};

