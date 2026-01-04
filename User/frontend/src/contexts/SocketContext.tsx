import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { AppState, AppStateStatus } from 'react-native';
import { io, Socket } from 'socket.io-client';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { getCurrentApiBaseUrl } from '../config/api';

interface SocketContextType {
  socket: Socket | null;
  isConnected: boolean;
  connect: () => void;
  disconnect: () => void;
}

const SocketContext = createContext<SocketContextType>({
  socket: null,
  isConnected: false,
  connect: () => { },
  disconnect: () => { },
});

export const useSocket = () => useContext(SocketContext);

interface SocketProviderProps {
  children: ReactNode;
}

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

      // Use the centralized API URL from config/api.ts
      // This ensures we use the same URL as the rest of the app (including .env vars)
      const fullApiUrl = getCurrentApiBaseUrl();

      // The getCurrentApiBaseUrl includes /api/v1, we need just the base host
      // e.g. http://192.168.1.5:8080/api/v1 -> http://192.168.1.5:8080
      // or https://my-backend.com/api/v1 -> https://my-backend.com
      let socketUrl = fullApiUrl;

      if (socketUrl.includes('/api/')) {
        socketUrl = socketUrl.split('/api/')[0];
      }

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

    const subscription = AppState.addEventListener('change', (nextAppState) => {
      if (nextAppState === 'active') {
        console.log('App active, reconnecting socket...');
        connect();
      } else if (nextAppState === 'background') {
        console.log('App background, disconnecting socket...');
        disconnect();
      }
    });

    return () => {
      subscription.remove();
      disconnect();
    };
  }, []);

  return (
    <SocketContext.Provider value={{ socket, isConnected, connect, disconnect }}>
      {children}
    </SocketContext.Provider>
  );
};

