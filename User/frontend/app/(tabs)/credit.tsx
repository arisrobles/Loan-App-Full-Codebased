import React, { useEffect, useMemo, useRef, useState } from "react";
import {
  ActivityIndicator,
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Dimensions,
  Animated,
} from "react-native";
import { LinearGradient } from "expo-linear-gradient";
import Svg, { Circle, G, Line, Text as SvgText } from "react-native-svg";
import { FontAwesome5 } from "@expo/vector-icons";
import { api } from "../../src/config/api";

const { width } = Dimensions.get("window");

const DashboardGauge = ({
  score = 654,
  maxScore = 850,
  size = 280,
  strokeWidth = 18,
}) => {
  const radius = (size - strokeWidth) / 2;
  const cx = size / 2;
  const cy = size / 2;
  const circumference = Math.PI * radius; // half circle

  // Angle now mapped to 180°
  const angleDeg = Math.max(0, Math.min(180, (score / maxScore) * 180));

  // Animate needle
  const needleAnim = useRef(new Animated.Value(0)).current;
  useEffect(() => {
    Animated.spring(needleAnim, {
      toValue: angleDeg,
      useNativeDriver: false,
      speed: 2,
      bounciness: 10,
    }).start();
  }, [angleDeg]);

  const rotateInterpolate = needleAnim.interpolate({
    inputRange: [0, 180],
    outputRange: ["-90deg", "90deg"], // sweep left → right
  });

  // Major / minor ticks
  const majorTicks = useMemo(() => {
    const arr = [];
    for (let a = 0; a <= 180; a += 30) arr.push(a);
    return arr;
  }, []);
  const minorTicks = useMemo(() => {
    const arr = [];
    for (let a = 0; a <= 180; a += 6) {
      if (a % 30 !== 0) arr.push(a);
    }
    return arr;
  }, []);

  // Segments across 180°
  const segments = [
    { frac: 0.3, color: "#EF4444" },
    { frac: 0.35, color: "#F59E0B" },
    { frac: 0.35, color: "#22C55E" },
  ];

  let accFrac = 0;
  const segmentCircles = segments.map((seg, idx) => {
    const segLen = circumference * seg.frac;
    const dasharray = `${segLen} ${circumference - segLen}`;
    const dashoffset = -circumference * accFrac;
    accFrac += seg.frac;
    return { ...seg, dasharray, dashoffset };
  });

  // Labels across half arc
  const labels = [
    { text: "Poor", frac: 0.05 },
    { text: "Fair", frac: 0.25 },
    { text: "Good", frac: 0.55 },
    { text: "Excellent", frac: 0.9 },
  ];

  const labelPositions = labels.map((l) => {
    const a = l.frac * 180;
    const rad = ((a - 90) * Math.PI) / 180;
    const r = radius - 18;
    return {
      ...l,
      x: cx + r * Math.cos(rad),
      y: cy + r * Math.sin(rad),
    };
  });

  return (
    <View style={{ width: size, height: size / 1.8, alignItems: "center" }}>
      <Svg width={size} height={size / 1.2}>
        {/* Base semi-circle */}
        <Circle
          cx={cx}
          cy={cy}
          r={radius}
          stroke="#E5E7EB"
          strokeWidth={strokeWidth}
          fill="none"
          strokeDasharray={`${circumference} ${circumference}`}
          strokeDashoffset={circumference}
          transform={`rotate(-90 ${cx} ${cy})`}
        />

        {/* Segments */}
        {segmentCircles.map((seg, i) => (
          <Circle
            key={`seg-${i}`}
            cx={cx}
            cy={cy}
            r={radius}
            stroke={seg.color}
            strokeWidth={strokeWidth}
            fill="none"
            strokeDasharray={seg.dasharray}
            strokeDashoffset={seg.dashoffset}
            transform={`rotate(-90 ${cx} ${cy})`}
            strokeLinecap="round"
          />
        ))}

        {/* Minor ticks */}
        {minorTicks.map((deg, idx) => {
          const rad = ((deg - 90) * Math.PI) / 180;
          const rOuter = radius + strokeWidth * 0.1;
          const rInner = rOuter - 8;
          return (
            <Line
              key={`minor-${idx}`}
              x1={cx + rOuter * Math.cos(rad)}
              y1={cy + rOuter * Math.sin(rad)}
              x2={cx + rInner * Math.cos(rad)}
              y2={cy + rInner * Math.sin(rad)}
              stroke="#CBD5E1"
              strokeWidth={1}
            />
          );
        })}

        {/* Major ticks */}
        {majorTicks.map((deg, idx) => {
          const rad = ((deg - 90) * Math.PI) / 180;
          const rOuter = radius + strokeWidth * 0.12;
          const rInner = rOuter - 16;
          return (
            <Line
              key={`major-${idx}`}
              x1={cx + rOuter * Math.cos(rad)}
              y1={cy + rOuter * Math.sin(rad)}
              x2={cx + rInner * Math.cos(rad)}
              y2={cy + rInner * Math.sin(rad)}
              stroke="#94A3B8"
              strokeWidth={2}
              strokeLinecap="round"
            />
          );
        })}

        {/* Labels */}
        {labelPositions.map((p, i) => (
          <SvgText
            key={`lbl-${i}`}
            x={p.x}
            y={p.y}
            fontSize="10"
            fill="#64748B"
            textAnchor="middle"
            alignmentBaseline="middle"
          >
            {p.text}
          </SvgText>
        ))}
      </Svg>

      {/* Needle + Center Text */}
      <View
        style={[
          StyleSheet.absoluteFill,
          { alignItems: "center", justifyContent: "flex-end", paddingBottom: 20 },
        ]}
      >
        <Animated.View
          style={[
            styles.needleContainer,
            { transform: [{ rotate: rotateInterpolate }] },
          ]}
        >
          <View style={styles.needle} />
          <View style={styles.needleHub} />
        </Animated.View>

        <View style={styles.centerBox}>
          <Text style={styles.centerTitle}>CREDIT SCORE</Text>
          <Text style={styles.centerScore}>
            {score} / {maxScore}
          </Text>
        </View>
      </View>
    </View>
  );
};

export default function CreditScoreDashboardScreen() {
  const [score, setScore] = useState(654);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({
    totalLoans: 0,
    approvedLoans: 0,
    scoreAvg: 654,
    pendingLoans: 0,
  });

  useEffect(() => {
    fetchCreditData();
  }, []);

  const fetchCreditData = async () => {
    try {
      const [scoreRes, loansRes] = await Promise.all([
        api.get('/credit/score'),
        api.get('/loans'),
      ]);

      if (scoreRes.data?.score) {
        setScore(scoreRes.data.score);
      }

      if (loansRes.data?.loans) {
        const loans = loansRes.data.loans;
        setStats({
          totalLoans: loans.length,
          approvedLoans: loans.filter((l: any) => l.status === 'approved' || l.status === 'disbursed').length,
          scoreAvg: scoreRes.data?.score || 654,
          pendingLoans: loans.filter((l: any) => l.status === 'new_application' || l.status === 'under_review').length,
        });
      }
    } catch (error) {
      console.error('Error fetching credit data:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: 'black' }}>
        <ActivityIndicator size="large" color="#22C55E" />
      </View>
    );
  }

  return (
    <ScrollView
      contentContainerStyle={{ paddingBottom: 28 }}
      style={{ flex: 1, backgroundColor: "black" }}
      showsVerticalScrollIndicator={false}
    >
      {/* Header gradient like a car cockpit */}
      <LinearGradient
        colors={["#0f0c29", "#302b63", "#24243e"]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.header}
      >
        <View style={styles.topBar}>
          <TouchableOpacity style={styles.iconBtn}>
            <FontAwesome5 name="arrow-left" size={16} color="#E8EEF7" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Credit Score</Text>
          <TouchableOpacity style={styles.iconBtn}>
            <FontAwesome5 name="calendar-alt" size={16} color="#E8EEF7" />
          </TouchableOpacity>
        </View>

        <Text style={styles.subHeader}>Dashboard</Text>

        <View style={{ alignItems: "center", marginTop: 10 }}>
          <DashboardGauge score={score} maxScore={850} size={width * 0.8} />
        </View>
      </LinearGradient>

      {/* Quick stats cards */}
      <View style={styles.cardsWrap}>
        <View style={styles.card}>
          <Text style={styles.cardLabel}>Total Loans</Text>
          <Text style={styles.cardValue}>
            {stats.totalLoans}
          </Text>
        </View>
        <View style={styles.card}>
          <Text style={styles.cardLabel}>Approved Loans</Text>
          <Text style={styles.cardValue}>
            {stats.approvedLoans}
          </Text>
        </View>
        <View style={styles.card}>
          <Text style={styles.cardLabel}>Score Avg</Text>
          <Text style={styles.cardValue}>
            {stats.scoreAvg}
          </Text>
        </View>
        <View style={styles.card}>
          <Text style={styles.cardLabel}>Pending Loans</Text>
          <Text style={styles.cardValue}>{stats.pendingLoans}</Text>
        </View>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  header: {
    paddingTop: 52,
    paddingBottom: 24,
    paddingHorizontal: 16,
    borderBottomLeftRadius: 28,
    borderBottomRightRadius: 28,
  },
  topBar: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  iconBtn: {
    width: 36,
    height: 36,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.2)",
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "rgba(255,255,255,0.06)",
  },
  headerTitle: {
    color: "#F3F6FF",
    fontSize: 16,
    fontWeight: "600",
    letterSpacing: 0.3,
  },
  subHeader: {
    color: "#E2E8F0",
    fontSize: 12,
    opacity: 0.9,
    marginTop: 12,
  },

  // Gauge center readout
  centerBox: {
    alignItems: "center",
    marginTop: 8,
  },
  centerTitle: {
    fontSize: 10,
    color: "#94A3B8",
    letterSpacing: 1.2,
    marginTop: 6,
  },
  centerScore: {
    fontSize: 28,
    color: "#E6EDF8",
    fontWeight: "800",
    marginTop: 6,
  },

  // Needle visuals
  needleContainer: {
    width: 0,
    height: 0,
    alignItems: "center",
    justifyContent: "center",
    marginTop: 50
  },
  needle: {
    width: 4,
    height: 106,
    backgroundColor: "#E2E8F0",
    borderRadius: 4,
    transform: [{ translateY: -45 }],
  },
  needleHub: {
    position: "absolute",
    width: 16,
    height: 16,
    backgroundColor: "#111827",
    borderRadius: 8,
    borderWidth: 3,
    borderColor: "#E2E8F0",
  },

  // Cards
  cardsWrap: {
    paddingHorizontal: 16,
    paddingTop: 16,
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "space-between",
  },
  card: {
    width: "48%",
    backgroundColor: "#141726",
    padding: 14,
    borderRadius: 18,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.06)",
    shadowColor: "#000",
    shadowOpacity: 0.25,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 4 },
  },
  cardLabel: {
    color: "#B7C1DA",
    fontSize: 12,
    marginBottom: 8,
  },
  cardValue: {
    color: "#F3F6FF",
    fontSize: 28,
    fontWeight: "700",
  },
  cardSub: {
    color: "#7A8099",
    fontSize: 14,
  },
  cardDelta: {
    color: "#F87171",
    fontSize: 14,
    fontWeight: "600",
  },
});
